<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$errors = [];
$notice = null;
$pdo = null;
$isConfigured = app_database_is_configured();
$hasUsers = false;
$currentUser = null;

if ($isConfigured) {
    try {
        $pdo = app_db();
        app_run_admin_migrations($pdo);
        app_run_portfolio_migrations($pdo);
        app_seed_portfolio_content($pdo);
        $hasUsers = admin_has_users($pdo);
        $currentUser = admin_current_user($pdo);
    } catch (Throwable $exception) {
        $errors[] = 'No se ha podido conectar con la base de datos. Revisa .env para local o config/database.php para Hostalia.';
    }
}

if ($pdo instanceof PDO && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $csrf = (string) ($_POST['csrf_token'] ?? '');

    if (!app_verify_csrf_token($csrf)) {
        $errors[] = 'Token de seguridad inválido. Recarga la página e inténtalo de nuevo.';
    } elseif ($action === 'setup' && !$hasUsers) {
        $remaining = admin_lock_remaining($pdo, 'setup');

        if ($remaining > 0) {
            $errors[] = 'Demasiados intentos. Espera ' . (string) ceil($remaining / 60) . ' minutos.';
        } else {
            $config = app_database_config();
            $configuredSetupKey = (string) ($config['setup_key'] ?? '');
            $submittedSetupKey = (string) ($_POST['setup_key'] ?? '');
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $confirm = (string) ($_POST['password_confirm'] ?? '');
            $errors = array_merge($errors, admin_username_errors($username), admin_password_errors($password));

            if (strlen($configuredSetupKey) < 24 || $configuredSetupKey === 'change-this-to-a-long-random-secret-before-first-admin') {
                $errors[] = 'Define una setup_key privada y larga en config/database.php antes de crear el primer admin.';
            } elseif (!hash_equals($configuredSetupKey, $submittedSetupKey)) {
                $errors[] = 'La clave privada de setup no es correcta.';
            }

            if (!hash_equals($password, $confirm)) {
                $errors[] = 'Las contraseñas no coinciden.';
            }

            if ($errors === []) {
                try {
                    $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash, created_at, updated_at) VALUES (:username, :password_hash, :created_at, :updated_at)');
                    $stmt->execute([
                        'username' => $username,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'created_at' => app_now(),
                        'updated_at' => app_now(),
                    ]);
                    admin_clear_attempts($pdo, 'setup');
                    admin_login_user($pdo, (int) $pdo->lastInsertId());
                    header('Location: /admin/');
                    exit;
                } catch (Throwable $exception) {
                    admin_record_failed_attempt($pdo, 'setup');
                    $errors[] = 'No se ha podido crear el administrador inicial.';
                }
            } else {
                admin_record_failed_attempt($pdo, 'setup');
            }
        }
    } elseif ($action === 'login' && $hasUsers) {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $remaining = max(
            admin_lock_remaining($pdo, 'login-ip'),
            admin_lock_remaining($pdo, 'login', $username)
        );

        if ($remaining > 0) {
            $errors[] = 'Demasiados intentos. Espera ' . (string) ceil($remaining / 60) . ' minutos.';
        } else {
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if (is_array($user) && password_verify($password, (string) $user['password_hash'])) {
                if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
                    $rehash = $pdo->prepare('UPDATE admin_users SET password_hash = :password_hash, updated_at = :updated_at WHERE id = :id');
                    $rehash->execute([
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'updated_at' => app_now(),
                        'id' => (int) $user['id'],
                    ]);
                }

                admin_clear_attempts($pdo, 'login-ip');
                admin_clear_attempts($pdo, 'login', $username);
                admin_login_user($pdo, (int) $user['id']);
                header('Location: /admin/');
                exit;
            }

            admin_record_failed_attempt($pdo, 'login-ip');
            admin_record_failed_attempt($pdo, 'login', $username);
            $errors[] = 'Usuario o contraseña incorrectos.';
        }
    }

    if ($errors === []) {
        $notice = 'Acción procesada.';
    }
}

if ($pdo instanceof PDO) {
    $hasUsers = admin_has_users($pdo);
    $currentUser = admin_current_user($pdo);
}
if (is_array($currentUser)) {
    header('Location: /admin/content.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Acceso · Jacoto Studio</title>
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/studio.css?v=<?= filemtime(APP_BASE_PATH . '/assets/css/studio.css') ?>">
    <script src="/assets/js/studio.js?v=<?= filemtime(APP_BASE_PATH . '/assets/js/studio.js') ?>" defer></script>
</head>
<body>
<main class="studio-auth">
    <section class="auth-story"><a class="studio-brand" href="/">jacoto<span>STUDIO / 01</span></a><div><h1>Detrás de<br>cada <span>detalle.</span></h1><p>Tu historia, tus proyectos y tu próxima versión. Un espacio para crear con intención.</p></div><small>PORTFOLIO INDEPENDIENTE / GIRONA</small></section>
    <section class="auth-panel"><div class="auth-card">
        <span class="studio-eyebrow">ACCESO PRIVADO</span><h2><?= $pdo instanceof PDO && !$hasUsers ? 'Tu estudio empieza aquí.' : 'Bienvenido de nuevo.' ?></h2><p>Entra para dar forma a tu portfolio.</p>
        <?php if (!$isConfigured): ?><div class="studio-notice is-error">Configura la base de datos en .env o config/database.php para activar el estudio.</div><?php endif; ?>
        <?php foreach ($errors as $error): ?><div class="studio-notice is-error" role="alert"><?= e($error) ?></div><?php endforeach; ?>
        <?php if ($notice): ?><div class="studio-notice" role="status"><?= e($notice) ?></div><?php endif; ?>
        <?php if ($pdo instanceof PDO): ?>
            <form method="post" action="/admin/">
                <input type="hidden" name="action" value="<?= $hasUsers ? 'login' : 'setup' ?>"><input type="hidden" name="csrf_token" value="<?= e(app_csrf_token()) ?>">
                <?php if (!$hasUsers): ?><label>Clave privada de configuración<input type="password" name="setup_key" required autocomplete="off"></label><?php endif; ?>
                <label>Usuario<input type="text" name="username" value="<?= e((string) ($_POST['username'] ?? '')) ?>" required autocomplete="username" autocapitalize="none" spellcheck="false" <?= $hasUsers ? '' : 'minlength="3" maxlength="32" pattern="[a-zA-Z0-9._-]{3,32}"' ?>></label>
                <label>Contraseña<span class="password-field"><input type="password" name="password" required autocomplete="<?= $hasUsers ? 'current-password' : 'new-password' ?>" <?= $hasUsers ? '' : 'minlength="12"' ?>><button type="button" data-password-toggle aria-label="Mostrar contraseña" aria-pressed="false">Ver</button></span></label>
                <?php if (!$hasUsers): ?><label>Repetir contraseña<input type="password" name="password_confirm" minlength="12" required autocomplete="new-password"></label><p class="admin-help">Mínimo 12 caracteres con mayúscula, minúscula, número y símbolo.</p><?php endif; ?>
                <button class="studio-button primary" type="submit"><?= $hasUsers ? 'Entrar al estudio' : 'Crear administrador' ?> <span aria-hidden="true">↗</span></button>
            </form>
        <?php endif; ?>
        <a class="auth-back" href="/">← Volver al portfolio</a><div class="auth-note">Tu espacio de trabajo. Cada cambio, en su sitio.</div>
    </div></section>
</main>
</body>
</html>
