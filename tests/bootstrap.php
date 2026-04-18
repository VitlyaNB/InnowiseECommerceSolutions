<?php

use DG\BypassFinals;

require_once dirname(__DIR__).'/vendor/autoload.php';

if (class_exists('DG\BypassFinals')) {
    BypassFinals::enable();
}

if ((getenv('DB_CONNECTION') ?: '') === 'mysql') {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: '';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    if ($database !== '') {
        $escapedDatabase = str_replace('`', '``', $database);
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            try {
                $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$escapedDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                break;
            } catch (Throwable $exception) {
                if ($attempt === 10) {
                    fwrite(STDERR, sprintf("[tests/bootstrap] Unable to ensure database '%s': %s\n", $database, $exception->getMessage()));
                } else {
                    usleep(500000);
                }
            }
        }
    }
}
