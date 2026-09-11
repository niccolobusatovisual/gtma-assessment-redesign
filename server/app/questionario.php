<?php
declare(strict_types=1);

// l'admin sceglie solo la posizione in questa tavolozza, mai un colore libero
const KM2R_COLORI = ['#f59e0b', '#10b981', '#8b5cf6', '#0ea5e9', '#3b82f6', '#f97316', '#84cc16', '#64748b'];

function km2r_carica_questionario(PDO $db, int $versioneId): array
{
    $leggi = function (string $sql) use ($db, $versioneId): array {
        $q = $db->prepare($sql);
        $q->execute([$versioneId]);
        return $q->fetchAll();
    };

    $sezioni = $leggi('SELECT id, chiave, nome, nome_breve, descrizione, colore_indice, benchmark_b2b, benchmark_b2c
        FROM sezioni WHERE versione_id = ? ORDER BY ordine, id');
    $target = $leggi('SELECT t.sezione_id, t.segmento, t.valore
        FROM target_sezione t JOIN obiettivi o ON o.id = t.obiettivo_id
        WHERE o.versione_id = ? ORDER BY o.ordine, o.id');
    $domande = $leggi('SELECT d.id, d.sezione_id, d.segmento, d.testo
        FROM domande d JOIN sezioni s ON s.id = d.sezione_id
        WHERE s.versione_id = ? ORDER BY d.ordine, d.id');
    $risposte = $leggi('SELECT r.domanda_id, r.testo, r.valore
        FROM risposte r JOIN domande d ON d.id = r.domanda_id JOIN sezioni s ON s.id = d.sezione_id
        WHERE s.versione_id = ? ORDER BY r.ordine, r.id');

    $targetPerSezione = [];
    foreach ($target as $t) {
        $targetPerSezione[$t['sezione_id']][$t['segmento']][] = (float) $t['valore'];
    }

    $opzioni = [];
    foreach ($risposte as $r) {
        $opzioni[$r['domanda_id']]['opts'][] = $r['testo'];
        $opzioni[$r['domanda_id']]['vals'][] = (float) $r['valore'];
    }

    $chiavi = [];
    $dims = [];
    $questions = [];
    $short = [];
    foreach ($sezioni as $s) {
        $chiavi[$s['id']] = $s['chiave'];
        $tgB2b = $targetPerSezione[$s['id']]['b2b'] ?? [];
        $tgB2c = $targetPerSezione[$s['id']]['b2c'] ?? [];
        $dims[] = [
            'id' => $s['chiave'],
            'label' => $s['nome'],
            'desc' => $s['descrizione'],
            'color' => KM2R_COLORI[(int) $s['colore_indice']] ?? KM2R_COLORI[0],
            'bm_b2b' => (float) $s['benchmark_b2b'],
            'bm_b2c' => (float) $s['benchmark_b2c'],
            'bm' => (float) $s['benchmark_b2b'],
            'tg' => $tgB2b[0] ?? 0.6,
            'tg_b2b' => $tgB2b,
            'tg_b2c' => $tgB2c,
        ];
        $questions[$s['chiave']] = ['b2b' => [], 'b2c' => []];
        $short[$s['chiave']] = $s['nome_breve'];
    }

    foreach ($domande as $d) {
        $questions[$chiavi[$d['sezione_id']]][$d['segmento']][] = [
            'text' => $d['testo'],
            'opts' => $opzioni[$d['id']]['opts'] ?? [],
            'vals' => $opzioni[$d['id']]['vals'] ?? [],
        ];
    }

    return ['dims' => $dims, 'questions' => $questions, 'short' => $short];
}

// sostituisce nella pagina i dati scritti a mano con quelli del database, negli stessi punti
function km2r_prepara_pagina(string $html, array $questionario, int $versioneId): string
{
    $js = function ($valore): string {
        return json_encode($valore, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    };

    $html = km2r_sostituisci_blocco($html, '// @@DIMS_START@@', '// @@DIMS_END@@',
        'const DIMS = ' . $js($questionario['dims']) . ';');
    $html = km2r_sostituisci_blocco($html, '// @@QUESTIONS_START@@', '// @@QUESTIONS_END@@',
        'const QUESTIONS = ' . $js((object) $questionario['questions']) . ';');
    $html = km2r_sostituisci_blocco($html, '// @@ACCESS_START@@', '// @@ACCESS_END@@',
        "var ACCESS_CODE = '';\nvar KM2R = " . $js(['versione_id' => $versioneId, 'api' => 'api/compilazione.php']) . ';');

    $trovati = 0;
    $html = preg_replace_callback('/var DIM_SHORT = \{[^}]*\};/', function () use ($js, $questionario): string {
        return 'var DIM_SHORT = ' . $js((object) $questionario['short']) . ';';
    }, $html, 1, $trovati);
    if ($trovati !== 1) {
        throw new RuntimeException('Nella pagina manca DIM_SHORT.');
    }
    return $html;
}

function km2r_sostituisci_blocco(string $html, string $inizio, string $fine, string $contenuto): string
{
    $a = strpos($html, $inizio);
    $b = $a === false ? false : strpos($html, $fine, $a);
    if ($a === false || $b === false) {
        throw new RuntimeException('Nella pagina manca il marcatore ' . $inizio);
    }
    return substr($html, 0, $a + strlen($inizio)) . "\n" . $contenuto . "\n" . substr($html, $b);
}
