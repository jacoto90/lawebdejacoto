<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('APP_BASE_PATH', $argv[1] ?? dirname(__DIR__));
require APP_BASE_PATH . '/app/bootstrap.php';
$pdo = app_db();
app_run_portfolio_migrations($pdo);
app_seed_portfolio_content($pdo);
$key = 'current_role_studio_20261020';
$check = $pdo->prepare('SELECT meta_value FROM portfolio_meta WHERE meta_key = :key');
$check->execute(['key' => $key]);
if ($check->fetchColumn() !== false) { echo "Content update already applied.\n"; exit; }

// Keep a private, restorable snapshot of affected content before editing it.
$backupPath = $argv[2] ?? null;
if (!$backupPath) throw new RuntimeException('Provide a private backup path as the second argument.');
$tables = ['portfolio_experiences', 'portfolio_experience_i18n', 'portfolio_experience_tags', 'portfolio_projects', 'portfolio_project_i18n', 'portfolio_hero_card_items', 'portfolio_meta'];
$backup = [];
foreach ($tables as $table) $backup[$table] = $pdo->query('SELECT * FROM ' . $table)->fetchAll();
if (file_put_contents($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new RuntimeException('Unable to save content backup.');
chmod($backupPath, 0600);
$profile = require APP_BASE_PATH . '/data/profile.php';
$position = $profile['experience'][0];
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT * FROM portfolio_experiences WHERE LOWER(company) = :name ORDER BY sort_order, id');
    $stmt->execute(['name' => 'fustes esteba']);
    $existing = $stmt->fetchAll();
    $id = $existing[0]['id'] ?? app_uid();
    app_upsert($pdo, 'portfolio_experiences', ['id'], ['id' => $id, 'company' => 'Fustes Esteba', 'start_date' => ($existing[0]['start_date'] ?? '') ?: $position['start_date'], 'end_date' => '', 'sort_order' => -100, 'is_active' => 1]);
    foreach (['es', 'en'] as $lang) app_upsert($pdo, 'portfolio_experience_i18n', ['experience_id', 'lang'], ['experience_id' => $id, 'lang' => $lang, 'period' => $position['period'][$lang], 'title' => $position['title'][$lang], 'summary' => $position['summary'][$lang]]);
    $stmt = $pdo->prepare('SELECT name FROM portfolio_experience_tags WHERE experience_id = :id ORDER BY sort_order');
    $stmt->execute(['id' => $id]);
    $tags = $position['tags'];
    foreach (array_column($stmt->fetchAll(), 'name') as $tag) if (!in_array(mb_strtolower($tag), array_map('mb_strtolower', $tags), true)) $tags[] = $tag;
    $stmt = $pdo->prepare('DELETE FROM portfolio_experience_tags WHERE experience_id = :id');
    $stmt->execute(['id' => $id]);
    foreach ($tags as $index => $tag) app_upsert($pdo, 'portfolio_experience_tags', ['id'], ['id' => app_uid(), 'experience_id' => $id, 'name' => $tag, 'sort_order' => $index]);
    $stmt = $pdo->prepare('UPDATE portfolio_experiences SET is_active = 0 WHERE LOWER(company) = :name AND id <> :id');
    $stmt->execute(['name' => 'fustes esteba', 'id' => $id]);
    $stmt = $pdo->prepare('UPDATE portfolio_projects SET is_active = 0 WHERE LOWER(name) = :name OR LOWER(url) LIKE :url');
    $stmt->execute(['name' => 'paso seguro', 'url' => '%pasoseguro.pro%']);
    $stmt = $pdo->prepare('UPDATE portfolio_hero_card_items SET is_active = 0 WHERE LOWER(label) = :name OR LOWER(url) LIKE :url');
    $stmt->execute(['name' => 'paso seguro', 'url' => '%pasoseguro.pro%']);
    app_upsert($pdo, 'portfolio_meta', ['meta_key'], ['meta_key' => $key, 'meta_value' => app_now()]);
    $pdo->commit();
    echo "Fustes Esteba updated; duplicate entries and Paso Seguro archived.\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
