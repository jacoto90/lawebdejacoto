<?php

declare(strict_types=1);

const ADMIN_MAX_ATTEMPTS = 5;
const ADMIN_LOCK_SECONDS = 900;

function admin_has_users(PDO $pdo): bool
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM admin_users');
    return (int) $stmt->fetchColumn() > 0;
}

function admin_current_user(PDO $pdo): ?array
{
    if (empty($_SESSION['admin_user_id'])) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, username, created_at, last_login_at FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['admin_user_id']]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}

function admin_password_errors(string $password): array
{
    $errors = [];

    if (strlen($password) < 12) {
        $errors[] = 'La contraseña debe tener al menos 12 caracteres.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'La contraseña debe incluir una minúscula.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'La contraseña debe incluir una mayúscula.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'La contraseña debe incluir un número.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'La contraseña debe incluir un símbolo.';
    }

    return $errors;
}

function admin_username_errors(string $username): array
{
    if (!preg_match('/^[a-zA-Z0-9._-]{3,32}$/', $username)) {
        return ['El usuario debe tener 3-32 caracteres y solo letras, números, punto, guion o guion bajo.'];
    }

    return [];
}

function admin_attempt_identifier(string $action, string $username = ''): string
{
    $ip = app_request_ip();
    return hash('sha256', $action . '|' . $ip . '|' . strtolower($username));
}

function admin_lock_remaining(PDO $pdo, string $action, string $username = ''): int
{
    $stmt = $pdo->prepare('SELECT locked_until FROM admin_login_attempts WHERE action = :action AND identifier_hash = :identifier LIMIT 1');
    $stmt->execute([
        'action' => $action,
        'identifier' => admin_attempt_identifier($action, $username),
    ]);
    $lockedUntil = $stmt->fetchColumn();

    if (!$lockedUntil) {
        return 0;
    }

    $remaining = strtotime((string) $lockedUntil) - time();
    return max(0, $remaining);
}

function admin_record_failed_attempt(PDO $pdo, string $action, string $username = ''): void
{
    $identifier = admin_attempt_identifier($action, $username);
    $now = app_now();

    $stmt = $pdo->prepare('SELECT attempts FROM admin_login_attempts WHERE action = :action AND identifier_hash = :identifier LIMIT 1');
    $stmt->execute(['action' => $action, 'identifier' => $identifier]);
    $attempts = $stmt->fetchColumn();

    if ($attempts === false) {
        $stmt = $pdo->prepare('INSERT INTO admin_login_attempts (action, identifier_hash, attempts, locked_until, last_attempt_at) VALUES (:action, :identifier, 1, NULL, :now)');
        $stmt->execute(['action' => $action, 'identifier' => $identifier, 'now' => $now]);
        return;
    }

    $nextAttempts = (int) $attempts + 1;
    $lockedUntil = $nextAttempts >= ADMIN_MAX_ATTEMPTS ? date('Y-m-d H:i:s', time() + ADMIN_LOCK_SECONDS) : null;

    $stmt = $pdo->prepare('UPDATE admin_login_attempts SET attempts = :attempts, locked_until = :locked_until, last_attempt_at = :now WHERE action = :action AND identifier_hash = :identifier');
    $stmt->execute([
        'attempts' => $nextAttempts,
        'locked_until' => $lockedUntil,
        'now' => $now,
        'action' => $action,
        'identifier' => $identifier,
    ]);
}

function admin_clear_attempts(PDO $pdo, string $action, string $username = ''): void
{
    $stmt = $pdo->prepare('DELETE FROM admin_login_attempts WHERE action = :action AND identifier_hash = :identifier');
    $stmt->execute([
        'action' => $action,
        'identifier' => admin_attempt_identifier($action, $username),
    ]);
}

function admin_login_user(PDO $pdo, int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = $userId;
    $_SESSION['last_regenerated_at'] = time();

    $stmt = $pdo->prepare('UPDATE admin_users SET last_login_at = :now WHERE id = :id');
    $stmt->execute(['now' => app_now(), 'id' => $userId]);
}
