<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

const KM2R_CANALI = ['Rivenditori', 'Distributori', 'Punti vendita', 'Rete diretta', 'E-commerce', 'Altro'];

function km2r_risposta(int $stato, array $dati): void
{
    http_response_code($stato);
    echo json_encode($dati, JSON_UNESCAPED_UNICODE);
    exit;
}

function km2r_testo(array $in, string $campo, int $max, bool $obbligatorio): ?string
{
    $valore = $in[$campo] ?? null;
    $testo = is_string($valore) ? trim($valore) : '';
    if ($testo === '') {
        if ($obbligatorio) {
            throw new InvalidArgumentException("Campo obbligatorio mancante: $campo");
        }
        return null;
    }
    // conta i caratteri senza richiedere mbstring; restituisce false se il testo non è UTF-8 valido
    $caratteri = preg_match_all('/./us', $testo);
    if ($caratteri === false || $caratteri > $max) {
        throw new InvalidArgumentException("Campo non valido: $campo");
    }
    return $testo;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    km2r_risposta(405, ['ok' => false, 'errore' => 'Metodo non consentito']);
}

// si accettano solo richieste dalla pagina dell'autovalutazione sullo stesso dominio
$origine = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
$sito = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? 'same-origin';
if (($origine !== '' && parse_url($origine, PHP_URL_HOST) !== $host) || $sito !== 'same-origin') {
    km2r_risposta(403, ['ok' => false, 'errore' => 'Richiesta non consentita']);
}

$in = json_decode((string) file_get_contents('php://input', false, null, 0, 20000), true);
if (!is_array($in)) {
    km2r_risposta(400, ['ok' => false, 'errore' => 'Dati non leggibili']);
}

$db = null;
try {
    $campi = [
        'compilatore_nome' => km2r_testo($in, 'compilatore_nome', 120, true),
        'compilatore_email' => km2r_testo($in, 'compilatore_email', 254, true),
        'azienda_nome' => km2r_testo($in, 'azienda_nome', 160, true),
        'ruolo' => km2r_testo($in, 'ruolo', 120, true),
        'settore' => km2r_testo($in, 'settore', 120, true),
        'fatturato' => km2r_testo($in, 'fatturato', 40, false),
        'dipendenti' => km2r_testo($in, 'dipendenti', 40, false),
        'clienti_anno' => km2r_testo($in, 'clienti_anno', 40, false),
        'descrizione' => km2r_testo($in, 'descrizione', 5000, false),
    ];
    if (filter_var($campi['compilatore_email'], FILTER_VALIDATE_EMAIL) === false) {
        throw new InvalidArgumentException('E-mail non valida');
    }

    $quota = filter_var($in['quota_b2b'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
    if ($quota === false) {
        throw new InvalidArgumentException('Quota B2B non valida');
    }
    $segmento = $in['segmento'] ?? '';
    if (!in_array($segmento, ['b2b', 'b2c'], true)) {
        throw new InvalidArgumentException('Segmento non valido');
    }
    $obiettivo = $in['obiettivo_ordine'] ?? null;
    if ($obiettivo !== null) {
        $obiettivo = filter_var($obiettivo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($obiettivo === false) {
            throw new InvalidArgumentException('Obiettivo non valido');
        }
    }
    $versioneId = filter_var($in['versione_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($versioneId === false) {
        throw new InvalidArgumentException('Versione del questionario non valida');
    }
    $canali = $in['canali'] ?? [];
    if (!is_array($canali)) {
        throw new InvalidArgumentException('Canali non validi');
    }
    foreach ($canali as $canale) {
        if (!is_string($canale) || !in_array($canale, KM2R_CANALI, true)) {
            throw new InvalidArgumentException('Canali non validi');
        }
    }
    if (($in['consenso'] ?? null) !== true) {
        throw new InvalidArgumentException('Manca il consenso privacy');
    }
    $punteggi = $in['punteggi'] ?? null;
    if (!is_array($punteggi)) {
        throw new InvalidArgumentException('Risultati mancanti');
    }
    foreach ($punteggi as $valore) {
        if (!(is_int($valore) || is_float($valore)) || $valore < 0 || $valore > 1) {
            throw new InvalidArgumentException('Punteggi non validi');
        }
    }

    $db = km2r_db();
    $versione = km2r_versione_attiva($db);
    if ($versione === null || (int) $versione['id'] !== $versioneId) {
        km2r_risposta(409, ['ok' => false, 'errore' => 'Il questionario è stato aggiornato: ricarica la pagina.']);
    }

    // benchmark e target si prendono dal database, non dal browser
    $q = $db->prepare("SELECT s.chiave, s.nome, s.ordine,
            CASE WHEN :segmento = 'b2b' THEN s.benchmark_b2b ELSE s.benchmark_b2c END AS benchmark,
            t.valore AS target
        FROM sezioni s
        LEFT JOIN obiettivi o ON o.versione_id = s.versione_id AND o.ordine = :obiettivo
        LEFT JOIN target_sezione t ON t.sezione_id = s.id AND t.obiettivo_id = o.id AND t.segmento = :segmento_target
        WHERE s.versione_id = :versione
        ORDER BY s.ordine, s.id");
    $q->execute([
        ':segmento' => $segmento,
        ':obiettivo' => $obiettivo ?? 1,
        ':segmento_target' => $segmento,
        ':versione' => $versioneId,
    ]);
    $sezioni = $q->fetchAll();

    if (count($sezioni) === 0 || count($sezioni) !== count($punteggi)) {
        throw new InvalidArgumentException('Risultati incompleti');
    }
    $totale = 0.0;
    foreach ($sezioni as $s) {
        if (!array_key_exists($s['chiave'], $punteggi)) {
            throw new InvalidArgumentException('Risultati incompleti');
        }
        $totale += (float) $punteggi[$s['chiave']];
    }
    $punteggio = $totale / count($sezioni);

    $obiettivoTesto = null;
    if ($obiettivo !== null) {
        $q = $db->prepare('SELECT testo FROM obiettivi WHERE versione_id = ? AND ordine = ?');
        $q->execute([$versioneId, $obiettivo]);
        $obiettivoTesto = $q->fetchColumn();
        if ($obiettivoTesto === false) {
            throw new InvalidArgumentException('Obiettivo non valido');
        }
    }

    $q = $db->prepare('SELECT soglia_positivo, soglia_sviluppo FROM impostazioni WHERE versione_id = ?');
    $q->execute([$versioneId]);
    $soglie = $q->fetch() ?: ['soglia_positivo' => 0.6, 'soglia_sviluppo' => 0.4];
    $percentuale = (int) round($punteggio * 100);
    if ($percentuale >= round((float) $soglie['soglia_positivo'] * 100)) {
        $esito = 'positivo';
    } elseif ($percentuale >= round((float) $soglie['soglia_sviluppo'] * 100)) {
        $esito = 'in sviluppo';
    } else {
        $esito = 'critico';
    }

    $db->beginTransaction();
    $q = $db->prepare('INSERT INTO compilazioni
        (ambiente, versione_id, compilatore_nome, compilatore_email, azienda_nome, ruolo, settore,
         fatturato, dipendenti, clienti_anno, quota_b2b, segmento, obiettivo_ordine, obiettivo_testo,
         canali, descrizione, punteggio, esito, consenso_il)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $q->execute([
        km2r_config()['ambiente'] === 'prova' ? 'prova' : 'online',
        $versioneId,
        $campi['compilatore_nome'],
        $campi['compilatore_email'],
        $campi['azienda_nome'],
        $campi['ruolo'],
        $campi['settore'],
        $campi['fatturato'],
        $campi['dipendenti'],
        $campi['clienti_anno'],
        $quota,
        $segmento,
        $obiettivo,
        $obiettivoTesto,
        $canali ? json_encode(array_values($canali), JSON_UNESCAPED_UNICODE) : null,
        $campi['descrizione'],
        round($punteggio, 5),
        $esito,
    ]);
    $id = (int) $db->lastInsertId();

    $q = $db->prepare('INSERT INTO risultati_sezione
        (compilazione_id, sezione_chiave, sezione_nome, ordine, punteggio, benchmark, target)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($sezioni as $s) {
        $q->execute([
            $id,
            $s['chiave'],
            $s['nome'],
            $s['ordine'],
            round((float) $punteggi[$s['chiave']], 5),
            $s['benchmark'],
            $s['target'] ?? 0.6,
        ]);
    }
    $db->commit();

    km2r_risposta(201, ['ok' => true, 'id' => $id]);
} catch (InvalidArgumentException $e) {
    km2r_risposta(422, ['ok' => false, 'errore' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Autovalutazione 2R, salvataggio: ' . $e->getMessage());
    km2r_risposta(500, ['ok' => false, 'errore' => 'Salvataggio non riuscito']);
}
