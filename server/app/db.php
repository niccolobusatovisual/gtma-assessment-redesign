<?php
declare(strict_types=1);

function km2r_config(): array
{
    static $config = null;
    if ($config === null) {
        $file = __DIR__ . '/config.php';
        if (!is_file($file)) {
            throw new RuntimeException('Manca il file app/config.php con i dati del database.');
        }
        $config = require $file;
    }
    return $config;
}

function km2r_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = km2r_config();
        $pdo = new PDO(
            'mysql:host=' . $c['db_host'] . ';dbname=' . $c['db_nome'] . ';charset=utf8mb4',
            $c['db_utente'],
            $c['db_password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    return $pdo;
}

// prova mostra la bozza se esiste, altrimenti la versione pubblicata; online solo la pubblicata
function km2r_versione_attiva(PDO $db): ?array
{
    $stati = km2r_config()['ambiente'] === 'prova' ? "'bozza','pubblicata'" : "'pubblicata'";
    $riga = $db->query("SELECT id, stato FROM versioni WHERE stato IN ($stati) ORDER BY stato = 'bozza' DESC LIMIT 1")->fetch();
    return $riga ?: null;
}
