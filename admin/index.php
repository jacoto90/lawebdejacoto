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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel JACOTO</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="admin-page">
<main class="admin-shell">
    <section class="admin-card">
        <p class="eyebrow">Acceso privado</p>
        <h1>Panel JACOTO</h1>

        <?php if (!$isConfigured): ?>
            <div class="admin-alert admin-alert-warning">
                Falta la configuración de base de datos. Para local, copia <code>.env.example</code> como <code>.env</code> y rellena tus datos MySQL. Para Hostalia también puedes usar <code>config/database.php</code>.
            </div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="admin-alert admin-alert-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <?php if ($notice): ?>
            <div class="admin-alert admin-alert-success"><?= e($notice) ?></div>
        <?php endif; ?>

        <?php if ($pdo instanceof PDO && is_array($currentUser)): ?>
            <div class="admin-dashboard">
                <p>Sesión iniciada como <strong><?= e((string) $currentUser['username']) ?></strong>.</p>
                <p class="admin-muted">Base segura activa: usuario admin, sesiones protegidas, CSRF y bloqueo de intentos.</p>
                <div class="admin-next-box">
                    <h2>Contenido editable</h2>
                    <p>Gestiona perfil, textos, skills, estudios, experiencia, servicios y proyectos desde base de datos.</p>
                </div>
                <a class="btn btn-primary" href="/admin/content.php">Editar portfolio</a>
                <form method="post" action="/admin/logout.php">
                    <input type="hidden" name="csrf_token" value="<?= e(app_csrf_token()) ?>">
                    <button class="btn btn-ghost" type="submit">Cerrar sesión</button>
                </form>
            </div>
        <?php elseif ($pdo instanceof PDO && !$hasUsers): ?>
            <p class="admin-muted">Crea el primer administrador. Este registro solo está disponible mientras no exista ningún usuario admin.</p>
            <form class="admin-form" method="post" action="/admin/" autocomplete="off" novalidate>
                <input type="hidden" name="action" value="setup">
                <input type="hidden" name="csrf_token" value="<?= e(app_csrf_token()) ?>">
                <label>
                    Clave privada de setup
                    <input type="password" name="setup_key" required autocomplete="off">
                </label>
                <label>
                    Usuario
                    <input type="text" name="username" minlength="3" maxlength="32" pattern="[a-zA-Z0-9._-]{3,32}" required autocomplete="username">
                </label>
                <label>
                    Contraseña
                    <input type="password" name="password" minlength="12" required autocomplete="new-password">
                </label>
                <label>
                    Repetir contraseña
                    <input type="password" name="password_confirm" minlength="12" required autocomplete="new-password">
                </label>
                <p class="admin-help">Mínimo 12 caracteres, mayúscula, minúscula, número y símbolo.</p>
                <button class="btn btn-primary" type="submit">Crear administrador</button>
            </form>
        <?php elseif ($pdo instanceof PDO): ?>
            <form class="admin-form" method="post" action="/admin/" autocomplete="off" novalidate>
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?= e(app_csrf_token()) ?>">
                <label>
                    Usuario
                    <input type="text" name="username" required autocomplete="username">
                </label>
                <label>
                    Contraseña
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <button class="btn btn-primary" type="submit">Entrar</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
