<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('APP_BASE_PATH', dirname(__DIR__));
require APP_BASE_PATH . '/app/bootstrap.php';
require APP_BASE_PATH . '/app/editor.php';

function verify(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}
function rejects(callable $action, int $status): void
{
    try { $action(); } catch (StudioError $error) { verify($error->getCode() === $status, 'Unexpected error: ' . $error->getMessage()); return; }
    throw new RuntimeException('Expected an error with status ' . $status);
}
function payload(PDO $pdo, string $entity, string $id, array $changes = []): array
{
    $record = studio_record($pdo, $entity, $id) ?? [];
    $data = ['entity' => $entity, 'id' => $id, 'revision' => studio_revision($pdo, $entity, $id), 'request_id' => bin2hex(random_bytes(16)), 'is_new' => $record ? '0' : '1'];
    foreach (studio_fields($entity) as $key => $field) $data[$key] = (string) ($record[$key] ?? ($field['type'] === 'checkbox' ? 1 : ($field['type'] === 'number' ? 99 : '')));
    if (isset(studio_schema()[$entity]['parent'])) { $key = studio_schema()[$entity]['parent']['field']; $data[$key] = $record[$key] ?? ''; }
    return array_replace($data, $changes);
}

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
app_run_portfolio_migrations($pdo);
app_seed_portfolio_content($pdo);
studio_migrate($pdo);
$original = app_portfolio_profile_from_db($pdo);
verify($original['experience'][0]['company'] === 'Fustes Esteba', 'Current role must be seeded.');
verify(app_experience_duration('2026-09-14', '2026-10-20', 'es') === '2 meses', 'Current role duration.');
verify(!in_array('Paso Seguro', array_column($original['projects'], 'name'), true), 'Retired project must not be seeded.');

// Each profile block updates only its own fields.
$bio = payload($pdo, 'intro', 'main', ['bio_es' => 'Integraciones SAP y ABAP.', 'bio_en' => '']);
$saved = studio_save($pdo, $bio);
verify($saved['revision'] === 1, 'First revision.');
$profile = app_portfolio_profile_from_db($pdo);
verify($profile['bio']['en'] === 'Integraciones SAP y ABAP.', 'English fallback.');
verify($profile['email'] === $original['email'] && $profile['hobby'] === $original['hobby'], 'Profile blocks must not erase each other.');
$replay = studio_save($pdo, $bio);
verify($replay['revision'] === 1 && $replay['replayed'], 'Retrying a successful request must be idempotent.');
rejects(fn() => studio_save($pdo, array_replace($bio, ['request_id' => bin2hex(random_bytes(16)), 'bio_es' => 'Stale overwrite'])), 409);
verify(studio_record($pdo, 'intro', 'main')['bio_es'] === 'Integraciones SAP y ABAP.', 'Stale edits must not overwrite data.');

// Invalid multilingual records are rejected without partial writes.
$job = studio_records($pdo, 'experience')[0];
$jobBefore = studio_record($pdo, 'experience', $job['id']);
rejects(fn() => studio_save($pdo, payload($pdo, 'experience', $job['id'], ['company' => 'Should not persist', 'end_date' => '2025-01-01'])), 422);
verify(studio_record($pdo, 'experience', $job['id']) === $jobBefore, 'Invalid save must be atomic.');
rejects(fn() => studio_save($pdo, payload($pdo, 'project', app_uid(), ['name' => 'Invalid', 'url' => 'javascript:alert(1)', 'type_es' => 'Test', 'description_es' => 'Test'])), 422);
rejects(fn() => studio_save($pdo, ['entity' => 'untrusted_table', 'id' => 'main']), 400);

// A new record can be retried without duplicates, archived and restored.
$id = app_uid();
$create = payload($pdo, 'project', $id, ['name' => 'Studio test', 'logo' => 'TS', 'url' => 'https://example.com', 'type_es' => 'Prueba', 'description_es' => 'Proyecto de prueba']);
studio_save($pdo, $create);
studio_save($pdo, $create);
verify((int) $pdo->query("SELECT COUNT(*) FROM portfolio_projects WHERE id = '$id'")->fetchColumn() === 1, 'Create retries must not duplicate records.');
studio_save($pdo, payload($pdo, 'project', $id, ['is_active' => '0']));
verify(!in_array('Studio test', array_column(app_portfolio_profile_from_db($pdo)['projects'], 'name'), true), 'Hidden content must not render publicly.');
verify(studio_record($pdo, 'project', $id)['description_es'] === 'Proyecto de prueba', 'Hiding must retain content.');
studio_save($pdo, payload($pdo, 'project', $id, ['is_active' => '1']));
verify(in_array('Studio test', array_column(app_portfolio_profile_from_db($pdo)['projects'], 'name'), true), 'Archived content can be restored.');

// Editing a text survives public migrations; cleared skill labels stay cleared.
studio_save($pdo, payload($pdo, 'text', 'hero_typewriter', ['value_es' => 'Una presentación personalizada.', 'value_en' => 'A custom introduction.']));
$skill = studio_records($pdo, 'skill')[0];
app_run_portfolio_migrations($pdo);
studio_save($pdo, payload($pdo, 'skill', $skill['id'], ['years_label' => '']));
app_run_portfolio_migrations($pdo);
app_seed_portfolio_content($pdo);
verify(studio_record($pdo, 'text', 'hero_typewriter')['value_es'] === 'Una presentación personalizada.', 'Migration must not overwrite edited copy.');
verify(studio_record($pdo, 'skill', $skill['id'])['years_label'] === '', 'Migration must not overwrite cleared fields.');

// Deleting an experience removes its translations/tags/children, never unrelated entries.
$childId = app_uid();
studio_save($pdo, payload($pdo, 'exp_project', $childId, ['experience_id' => $job['id'], 'text_value_es' => 'Proyecto hijo']));
$delete = payload($pdo, 'experience', $job['id'], ['operation' => 'delete']);
studio_save($pdo, $delete);
verify(studio_record($pdo, 'exp_project', $childId) === null, 'Cascade removes child projects.');
verify(studio_record($pdo, 'project', $id) !== null, 'Delete must not affect unrelated projects.');
rejects(fn() => studio_save($pdo, $jobBefore + ['entity' => 'experience', 'revision' => 0, 'request_id' => bin2hex(random_bytes(16)), 'is_new' => '0']), 409);
rejects(fn() => studio_save($pdo, payload($pdo, 'exp_project', app_uid(), ['experience_id' => $job['id'], 'text_value_es' => 'Orphan'])), 404);
echo "PASS: current content, partial profile saves, language fallback, validation, atomicity, idempotency, revision conflicts, archive/restore, persistent edits and cascade deletion.\n";
