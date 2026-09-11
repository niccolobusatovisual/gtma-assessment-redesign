<?php
declare(strict_types=1);

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/questionario.php';

header('Content-Type: text/html; charset=utf-8');

// Header di sicurezza. La pagina ha gia' una CSP in un <meta>, ma frame-ancestors
// viene ignorato dentro un <meta>: l'anti-clickjacking funziona solo da qui.
// 'unsafe-inline' resta necessario finche' script, stili e handler onclick sono inline.
// frame-ancestors 'self': la pagina puo' essere messa in un iframe solo dallo stesso
// dominio. Per incorporarla su un altro sottodominio (es. www.keymove.it) aggiungere
// qui quell'origine, altrimenti l'iframe resta bianco.
header("Content-Security-Policy: default-src 'self'; base-uri 'none'; object-src 'none';"
    . " script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';"
    . " img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'none';"
    . " frame-src 'none'; frame-ancestors 'self'; upgrade-insecure-requests");
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=()');

try {
    if (km2r_config()['ambiente'] === 'prova') {
        header('X-Robots-Tag: noindex, nofollow');
    }
    $db = km2r_db();
    $versione = km2r_versione_attiva($db);
    if ($versione === null) {
        throw new RuntimeException('Nel database non c\'è nessuna versione del questionario da mostrare.');
    }
    $versioneId = (int) $versione['id'];
    $percorsoPagina = __DIR__ . '/app/pagina.html';
    if (!is_file($percorsoPagina)) {
        throw new RuntimeException('Manca il file app/pagina.html.');
    }
    $pagina = (string) file_get_contents($percorsoPagina);
    echo km2r_prepara_pagina($pagina, km2r_carica_questionario($db, $versioneId), $versioneId);
} catch (Throwable $e) {
    error_log('Autovalutazione 2R: ' . $e->getMessage());
    http_response_code(503);
    header('X-Robots-Tag: noindex, nofollow');
    // il messaggio di PDO può contenere il nome utente del database: non va mostrato
    $motivo = $e instanceof PDOException
        ? 'Connessione o lettura del database non riuscita: controlla i dati in app/config.php.'
        : $e->getMessage();
    echo '<!doctype html><html lang="it"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Autovalutazione non disponibile</title></head>'
        . '<body style="font-family:system-ui,sans-serif;padding:24px;line-height:1.5">'
        . '<h1 style="font-size:1.3rem">Autovalutazione momentaneamente non disponibile</h1>'
        . '<p>' . htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
}
