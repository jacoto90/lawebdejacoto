<?php

declare(strict_types=1);

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', dirname(__DIR__));
}

require __DIR__ . '/env.php';
require __DIR__ . '/database.php';
require __DIR__ . '/security.php';
require __DIR__ . '/admin_auth.php';
require __DIR__ . '/content.php';

app_send_admin_security_headers();
app_start_secure_session();
