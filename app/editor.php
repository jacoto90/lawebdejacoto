<?php

declare(strict_types=1);
require_once __DIR__ . '/editor_schema.php';

final class StudioError extends RuntimeException
{
    public array $fields;
    public function __construct(string $message, int $status = 422, array $fields = [])
    {
        parent::__construct($message, $status);
        $this->fields = $fields;
    }
}

function studio_migrate(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_editor_versions (resource VARCHAR(120) PRIMARY KEY, revision INT NOT NULL DEFAULT 0, request_id VARCHAR(64) NOT NULL DEFAULT \'\', updated_at VARCHAR(32) NOT NULL DEFAULT \'\')');
}

function studio_revision(PDO $pdo, string $entity, string $id): int
{
    $stmt = $pdo->prepare('SELECT revision FROM portfolio_editor_versions WHERE resource = :resource');
    $stmt->execute(['resource' => $entity . ':' . $id]);
    return (int) $stmt->fetchColumn();
}

function studio_fields(string $entity): array
{
    $config = studio_schema()[$entity];
    if ($entity === 'text') {
        return ['value_es' => studio_field('Español', 'textarea', false, 12000), 'value_en' => studio_field('Inglés', 'textarea', false, 12000)];
    }
    $fields = $config['fields'] + ($config['extra'] ?? []);
    foreach ($config['i18n']['fields'] ?? [] as $key => $field) {
        foreach (['es', 'en'] as $lang) {
            $fields[$key . '_' . $lang] = $field + ['lang' => $lang];
            if ($lang === 'en') $fields[$key . '_' . $lang]['required'] = false;
        }
    }
    return $fields;
}

function studio_record(PDO $pdo, string $entity, string $id): ?array
{
    $config = studio_schema()[$entity];
    if ($entity === 'text') {
        $defaults = studio_text_defaults();
        if (!array_key_exists($id, $defaults['es'])) return null;
        $record = ['id' => $id, 'value_es' => $defaults['es'][$id], 'value_en' => $defaults['en'][$id] ?? $defaults['es'][$id]];
        $stmt = $pdo->prepare('SELECT lang, text_value FROM portfolio_texts WHERE text_key = :id');
        $stmt->execute(['id' => $id]);
        foreach ($stmt->fetchAll() as $row) $record['value_' . $row['lang']] = $row['text_value'];
        return $record;
    }
    $stmt = $pdo->prepare('SELECT * FROM ' . $config['table'] . ' WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $record = $stmt->fetch();
    if (!$record) return null;
    if (isset($config['i18n'])) {
        $translation = $config['i18n'];
        $stmt = $pdo->prepare('SELECT * FROM ' . $translation['table'] . ' WHERE ' . $translation['foreign'] . ' = :id');
        $stmt->execute(['id' => $id]);
        foreach ($stmt->fetchAll() as $row) {
            foreach ($translation['fields'] as $key => $_) $record[$key . '_' . $row['lang']] = $row[$key];
        }
    }
    if ($entity === 'experience') {
        $stmt = $pdo->prepare('SELECT name FROM portfolio_experience_tags WHERE experience_id = :id ORDER BY sort_order');
        $stmt->execute(['id' => $id]);
        $record['tags'] = implode(', ', array_column($stmt->fetchAll(), 'name'));
    }
    return $record;
}

function studio_records(PDO $pdo, string $entity, ?string $parent = null): array
{
    $config = studio_schema()[$entity];
    if (!empty($config['singleton'])) return [studio_record($pdo, $entity, 'main')];
    if ($entity === 'text') return array_map(static fn($id) => studio_record($pdo, 'text', $id), array_keys(studio_text_defaults()['es']));
    $sql = 'SELECT id FROM ' . $config['table'];
    if (isset($config['parent'])) $sql .= ' WHERE ' . $config['parent']['field'] . ' = :parent';
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(isset($config['parent']) ? ['parent' => $parent] : []);
    return array_map(static fn($row) => studio_record($pdo, $entity, $row['id']), $stmt->fetchAll());
}

function studio_title(string $entity, array $record): string
{
    $config = studio_schema()[$entity];
    return $entity === 'text' ? studio_text_label($record['id']) : (string) ($record[$config['title'] ?? ''] ?? $config['label']);
}

function studio_valid_link(string $value): bool
{
    if ($value === '') return true;
    if (preg_match('/[\x00-\x20\\\\]/', $value) || str_starts_with($value, '//')) return false;
    if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $value)) {
        return preg_match('~^https?://~i', $value) === 1 && filter_var(str_replace('%lang%', 'es', $value), FILTER_VALIDATE_URL) !== false;
    }
    return true;
}

function studio_validate(string $entity, array $input): array
{
    $data = [];
    $errors = [];
    foreach (studio_fields($entity) as $key => $field) {
        if (!array_key_exists($key, $input) || !is_scalar($input[$key])) {
            $errors[$key] = 'Falta este campo. Recarga el editor antes de guardar.';
            continue;
        }
        $value = trim((string) $input[$key]);
        if ($field['required'] && $value === '') $errors[$key] = 'Completa ' . mb_strtolower($field['label']) . '.';
        if (mb_strlen($value) > $field['max']) $errors[$key] = 'Máximo ' . $field['max'] . ' caracteres.';
        if ($field['type'] === 'checkbox') {
            if (!in_array($value, ['0', '1'], true)) $errors[$key] = 'Valor de visibilidad inválido.';
            $value = (int) $value;
        } elseif ($field['type'] === 'number') {
            if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < -9999 || (int) $value > 9999) $errors[$key] = 'Usa un número entre -9999 y 9999.';
            $value = (int) $value;
        } elseif ($field['type'] === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$key] = 'Introduce un email válido.';
        } elseif (in_array($field['type'], ['url', 'path'], true) && !studio_valid_link($value)) {
            $errors[$key] = 'Usa una URL http(s), un ancla o una ruta relativa válida.';
        } elseif ($field['type'] === 'date' && $value !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) $errors[$key] = 'Introduce una fecha válida.';
        }
        $data[$key] = $value;
    }
    if ($entity === 'experience') {
        if (!empty($data['end_date']) && (empty($data['start_date']) || $data['end_date'] < $data['start_date'])) $errors['end_date'] = 'La fecha de fin debe ser posterior al inicio.';
        foreach (explode(',', (string) ($data['tags'] ?? '')) as $tag) {
            if (mb_strlen(trim($tag)) > 80) $errors['tags'] = 'Cada etiqueta admite hasta 80 caracteres.';
        }
    }
    if ($errors) throw new StudioError('Revisa los campos marcados. Los cambios todavía no se han guardado.', 422, $errors);
    return $data;
}

function studio_delete(PDO $pdo, string $entity, string $id): void
{
    $config = studio_schema()[$entity];
    if (!empty($config['singleton']) || $entity === 'text') throw new StudioError('Este bloque no se puede eliminar.');
    foreach (studio_schema() as $child => $childConfig) {
        if (($childConfig['parent']['entity'] ?? '') !== $entity) continue;
        foreach (studio_records($pdo, $child, $id) as $record) studio_delete($pdo, $child, $record['id']);
    }
    if ($entity === 'experience') {
        $stmt = $pdo->prepare('DELETE FROM portfolio_experience_tags WHERE experience_id = :id');
        $stmt->execute(['id' => $id]);
    }
    if (isset($config['i18n'])) {
        $stmt = $pdo->prepare('DELETE FROM ' . $config['i18n']['table'] . ' WHERE ' . $config['i18n']['foreign'] . ' = :id');
        $stmt->execute(['id' => $id]);
    }
    $stmt = $pdo->prepare('DELETE FROM ' . $config['table'] . ' WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function studio_save(PDO $pdo, array $input): array
{
    $entity = (string) ($input['entity'] ?? '');
    $config = studio_schema()[$entity] ?? null;
    $id = (string) ($input['id'] ?? '');
    $requestId = (string) ($input['request_id'] ?? '');
    $operation = (string) ($input['operation'] ?? 'save');
    if (!$config || !in_array($operation, ['save', 'delete'], true)) throw new StudioError('Operación desconocida.', 400);
    if (!preg_match('/^[a-zA-Z0-9_-]{1,80}$/', $id) || (!empty($config['singleton']) && $id !== 'main')) throw new StudioError('Identificador inválido.', 400);
    if ($entity !== 'text' && strlen($id) > 16) throw new StudioError('Identificador demasiado largo.', 400);
    if ($entity === 'text' && !array_key_exists($id, studio_text_defaults()['es'])) throw new StudioError('Texto desconocido.', 400);
    if (!preg_match('/^[a-f0-9-]{16,64}$/i', $requestId)) throw new StudioError('Identificador de guardado inválido.', 400);
    if (filter_var($input['revision'] ?? null, FILTER_VALIDATE_INT) === false || (int) $input['revision'] < 0) throw new StudioError('Versión inválida.', 400);
    $data = $operation === 'save' ? studio_validate($entity, $input) : [];
    $resource = $entity . ':' . $id;
    $pdo->beginTransaction();
    try {
        $insert = app_db_driver($pdo) === 'sqlite' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
        $stmt = $pdo->prepare($insert . ' INTO portfolio_editor_versions (resource, revision, request_id, updated_at) VALUES (:resource, 0, \'\', \'\')');
        $stmt->execute(['resource' => $resource]);
        $lock = app_db_driver($pdo) === 'mysql' ? ' FOR UPDATE' : '';
        $stmt = $pdo->prepare('SELECT revision, request_id FROM portfolio_editor_versions WHERE resource = :resource' . $lock);
        $stmt->execute(['resource' => $resource]);
        $version = $stmt->fetch();
        if ($version['request_id'] === $requestId) {
            $pdo->commit();
            return ['ok' => true, 'revision' => (int) $version['revision'], 'id' => $id, 'deleted' => $operation === 'delete', 'replayed' => true];
        }
        if ((int) $input['revision'] !== (int) $version['revision']) throw new StudioError('Este bloque ha cambiado en otra pestaña. Recarga su versión actual antes de aplicar tu borrador.', 409);
        if ($entity !== 'text') {
            $recordLock = $pdo->prepare('SELECT id FROM ' . $config['table'] . ' WHERE id = :id' . $lock);
            $recordLock->execute(['id' => $id]);
            $recordLock->fetchColumn();
        }
        $existing = studio_record($pdo, $entity, $id);
        if (!$existing && (($input['is_new'] ?? '') !== '1' || $operation === 'delete')) throw new StudioError('Este contenido ya no existe. Recarga el editor.', 404);
        if (!$existing && (int) $version['revision'] > 0) throw new StudioError('Este contenido fue eliminado. Recarga el editor.', 409);
        if (isset($config['parent'])) {
            $parentId = (string) ($input[$config['parent']['field']] ?? '');
            $parentTable = studio_schema()[$config['parent']['entity']]['table'];
            $parentLock = $pdo->prepare('SELECT id FROM ' . $parentTable . ' WHERE id = :id' . $lock);
            $parentLock->execute(['id' => $parentId]);
            if (!$parentLock->fetchColumn()) throw new StudioError('La sección principal ya no existe.', 404);
            if (!studio_record($pdo, $config['parent']['entity'], $parentId)) throw new StudioError('La sección principal ya no existe.', 404);
            if ($existing && $existing[$config['parent']['field']] !== $parentId) throw new StudioError('No puedes cambiar el bloque principal de este contenido.', 422);
        }
        if ($operation === 'delete') {
            studio_delete($pdo, $entity, $id);
        } elseif ($entity === 'text') {
            foreach (['es', 'en'] as $lang) app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => $id, 'lang' => $lang, 'text_value' => $data['value_' . $lang] !== '' || $lang === 'es' ? $data['value_' . $lang] : $data['value_es']]);
        } else {
            $base = ['id' => $id];
            foreach ($config['fields'] as $key => $_) $base[$key] = $data[$key];
            if (isset($config['parent'])) $base[$config['parent']['field']] = $parentId;
            if ($config['table'] === 'portfolio_profile') $base['updated_at'] = app_now();
            if (count($base) > 1) app_upsert($pdo, $config['table'], ['id'], $base);
            if (isset($config['i18n'])) {
                $translation = $config['i18n'];
                foreach (['es', 'en'] as $lang) {
                    $row = [$translation['foreign'] => $id, 'lang' => $lang];
                    foreach ($translation['fields'] as $key => $_) $row[$key] = $lang === 'en' && $data[$key . '_en'] === '' ? $data[$key . '_es'] : $data[$key . '_' . $lang];
                    app_upsert($pdo, $translation['table'], [$translation['foreign'], 'lang'], $row);
                }
            }
            if ($entity === 'experience') {
                $stmt = $pdo->prepare('DELETE FROM portfolio_experience_tags WHERE experience_id = :id');
                $stmt->execute(['id' => $id]);
                $tags = array_values(array_unique(array_filter(array_map('trim', explode(',', $data['tags'])))));
                foreach ($tags as $index => $tag) app_upsert($pdo, 'portfolio_experience_tags', ['id'], ['id' => app_uid(), 'experience_id' => $id, 'name' => $tag, 'sort_order' => $index]);
            }
        }
        $next = (int) $version['revision'] + 1;
        $stmt = $pdo->prepare('UPDATE portfolio_editor_versions SET revision = :revision, request_id = :request_id, updated_at = :time WHERE resource = :resource');
        $stmt->execute(['revision' => $next, 'request_id' => $requestId, 'time' => app_now(), 'resource' => $resource]);
        $record = $operation === 'delete' ? null : studio_record($pdo, $entity, $id);
        $pdo->commit();
        return ['ok' => true, 'id' => $id, 'revision' => $next, 'deleted' => $operation === 'delete', 'title' => $record ? studio_title($entity, $record) : '', 'active' => (int) ($record['is_active'] ?? 1), 'saved_at' => date(DATE_ATOM)];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
