<?php

declare(strict_types=1);

function app_database_config_path(): string
{
    return dirname(__DIR__) . '/config/database.php';
}

function app_database_is_configured(): bool
{
    if (is_file(app_database_config_path())) {
        return true;
    }

    return app_env('DB_CONNECTION') === 'sqlite'
        || app_env('DB_HOST') !== null
        && app_env('DB_DATABASE') !== null
        && app_env('DB_USERNAME') !== null;
}

function app_database_config(): array
{
    $path = app_database_config_path();
    if (is_file($path)) {
        $config = require $path;
        if (!is_array($config)) {
            throw new RuntimeException('Database configuration must return an array.');
        }
    } else {
        $config = [
            'connection' => app_env('DB_CONNECTION', 'mysql'),
            'host' => app_env('DB_HOST'),
            'database' => app_env('DB_DATABASE'),
            'username' => app_env('DB_USERNAME'),
            'password' => app_env('DB_PASSWORD', ''),
            'charset' => app_env('DB_CHARSET', 'utf8mb4'),
            'auto_create' => app_env('DB_AUTO_CREATE', 'false') === 'true',
            'setup_key' => app_env('ADMIN_SETUP_KEY'),
        ];
    }

    $config['connection'] = $config['connection'] ?? 'mysql';

    if ($config['connection'] === 'sqlite') {
        if (empty($config['database'])) {
            throw new RuntimeException('SQLite database path is missing.');
        }

        return $config;
    }

    foreach (['host', 'database', 'username'] as $key) {
        if (!array_key_exists($key, $config) || $config[$key] === null || $config[$key] === '') {
            throw new RuntimeException('Database configuration is incomplete.');
        }
    }

    $config['password'] = $config['password'] ?? '';
    $config['charset'] = $config['charset'] ?? 'utf8mb4';
    $config['auto_create'] = (bool) ($config['auto_create'] ?? false);

    return $config;
}

function app_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_database_config();

    if (($config['connection'] ?? 'mysql') === 'sqlite') {
        $databasePath = (string) $config['database'];
        if (!preg_match('/^[A-Za-z]:[\/\\\\]/', $databasePath) && strpos($databasePath, DIRECTORY_SEPARATOR) !== 0) {
            $databasePath = dirname(__DIR__) . '/' . ltrim($databasePath, '/\\');
        }

        $directory = dirname($databasePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdo = new PDO('sqlite:' . $databasePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }

    if ($config['auto_create']) {
        $serverDsn = sprintf('mysql:host=%s;charset=%s', $config['host'], $config['charset']);
        $serverPdo = new PDO($serverDsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $databaseName = str_replace('`', '``', (string) $config['database']);
        $serverPdo->exec('CREATE DATABASE IF NOT EXISTS `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['host'], $config['database'], $config['charset']);

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function app_run_admin_migrations(PDO $pdo): void
{
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    last_login_at TEXT NULL
)
SQL);

        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS admin_login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    action TEXT NOT NULL,
    identifier_hash TEXT NOT NULL,
    attempts INTEGER NOT NULL DEFAULT 0,
    locked_until TEXT NULL,
    last_attempt_at TEXT NOT NULL,
    UNIQUE(action, identifier_hash)
)
SQL);

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_admin_login_attempts_locked_until ON admin_login_attempts (locked_until)');
        return;
    }

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS admin_login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(32) NOT NULL,
    identifier_hash CHAR(64) NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_attempt_at DATETIME NOT NULL,
    UNIQUE KEY uniq_action_identifier (action, identifier_hash),
    KEY idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
}
