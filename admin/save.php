<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/editor.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        throw new StudioError('Usa POST para guardar.', 405);
    }
    $pdo = app_db();
    if (!admin_current_user($pdo)) throw new StudioError('Tu sesión ha terminado. Inicia sesión de nuevo para guardar.', 401);
    if (!app_verify_csrf_token($_POST['csrf_token'] ?? '')) throw new StudioError('La sesión ha cambiado. Recarga el editor para continuar.', 403);
    // Schema creation is performed by the authenticated editor, outside write transactions.
    $result = studio_save($pdo, $_POST);
    if (!str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        $section = studio_schema()[(string) $_POST['entity']]['section'];
        header('Location: /admin/content.php?section=' . rawurlencode($section) . '&saved=1', true, 303);
        exit;
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (StudioError $exception) {
    http_response_code($exception->getCode());
    echo json_encode(['ok' => false, 'message' => $exception->getMessage(), 'fields' => $exception->fields], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log('Portfolio studio save: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'No se ha podido guardar. Tus cambios siguen en el editor; vuelve a intentarlo.'], JSON_UNESCAPED_UNICODE);
}
