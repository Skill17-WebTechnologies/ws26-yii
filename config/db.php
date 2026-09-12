<?php
/**
 * Database configuration — MySQL.
 *
 * Every connection detail comes from the environment: .env while you develop
 * locally, .env.prod once the container is deployed (the entrypoint copies it
 * over .env on start). Nothing is hardcoded here, so there is exactly one place
 * to change credentials and no way for the code to disagree with the config.
 *
 * There is deliberately NO fallback connection. A template that quietly falls
 * back to some other database when configuration is missing looks healthy while
 * running against the wrong data — so missing values simply produce no DSN, and
 * /api/db-check reports which ones are absent.
 */

require_once __DIR__ . '/env.php';

$host     = ws_env('DB_HOST');
$port     = ws_env('DB_PORT') ?? '3306';
$database = ws_env('DB_DATABASE');

return [
    'class' => \yii\db\Connection::class,
    // Empty when the configuration has not arrived; Yii then fails on first use
    // and /api/db-check explains why, rather than connecting somewhere else.
    'dsn' => $host !== null && $database !== null
        ? "mysql:host={$host};port={$port};dbname={$database}"
        : '',
    'username' => ws_env('DB_USERNAME') ?? '',
    'password' => ws_env('DB_PASSWORD') ?? '',
    'charset' => 'utf8mb4',

    /*
     * Every project a competitor creates shares ONE MySQL database, so every
     * table this app creates is prefixed. Yii expands {{%visitor}} to
     * `yii_visitor`, keeping it apart from the competitor's other applications.
     * Rename the prefix per project; do not drop it.
     */
    'tablePrefix' => ws_env('DB_TABLE_PREFIX') ?? 'yii_',
];
