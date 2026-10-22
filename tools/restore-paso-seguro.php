<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('APP_BASE_PATH', $argv[1] ?? dirname(__DIR__));
require APP_BASE_PATH . '/app/bootstrap.php';
require APP_BASE_PATH . '/app/editor.php';
$pdo = app_db();
if (!app_portfolio_is_seeded($pdo)) throw new RuntimeException('Initialize the portfolio database before applying this content update.');
studio_migrate($pdo);
$key = 'paso_seguro_landing_20261022';
$check = $pdo->prepare('SELECT meta_value FROM portfolio_meta WHERE meta_key = :key');
$check->execute(['key' => $key]);
if ($check->fetchColumn() !== false) { echo "Paso Seguro update already applied.\n"; exit; }

$profile = require APP_BASE_PATH . '/data/profile.php';
$project = array_values(array_filter($profile['projects'], static fn(array $item): bool => ($item['logo'] ?? '') === 'PS'))[0];
$matches = $pdo->query("SELECT id FROM portfolio_projects WHERE LOWER(name) = 'paso seguro' OR LOWER(url) LIKE '%pasoseguro.pro%' OR logo = 'PS'")->fetchAll();
if (count($matches) > 1) throw new RuntimeException('Multiple Paso Seguro records found; review them before restoring.');
$id = $matches[0]['id'] ?? app_uid();
// Capture the revision before reading: Studio rejects concurrent edits.
$revision = studio_revision($pdo, 'project', $id);
$record = studio_record($pdo, 'project', $id);
$desired = [
    'name' => $project['name'], 'logo' => $project['logo'], 'url' => $project['url'],
    'sort_order' => $record['sort_order'] ?? ((int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM portfolio_projects')->fetchColumn() + 1),
    'is_active' => 1,
    'type_es' => $project['type']['es'], 'type_en' => $project['type']['en'],
    'description_es' => $project['description']['es'], 'description_en' => $project['description']['en'],
];
$changed = !$record;
foreach ($desired as $field => $value) {
    if ((string) ($record[$field] ?? '') !== (string) $value) $changed = true;
}
if ($changed) {
    $backupPath = $argv[2] ?? '';
    if ($backupPath === '') throw new RuntimeException('Provide a private backup filename as the second argument.');
    $backup = fopen($backupPath, 'x');
    if ($backup === false) throw new RuntimeException('Unable to create a new backup; existing backups are never overwritten.');
    $snapshot = json_encode(['entity' => 'project', 'id' => $id, 'revision' => $revision, 'record' => $record], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    try {
        if (fwrite($backup, $snapshot) !== strlen($snapshot)) throw new RuntimeException('Incomplete backup.');
    } finally {
        fclose($backup);
    }
    chmod($backupPath, 0600);
    studio_save($pdo, $desired + [
        'entity' => 'project', 'id' => $id, 'revision' => $revision,
        'request_id' => bin2hex(random_bytes(16)), 'is_new' => $record ? '0' : '1',
    ]);
}
app_upsert($pdo, 'portfolio_meta', ['meta_key'], ['meta_key' => $key, 'meta_value' => app_now()]);
echo "Paso Seguro is linked to the internal landing.\n";
