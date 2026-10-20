<?php

declare(strict_types=1);

function studio_icon(string $name): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V3h8v4M3 12a25 25 0 0 0 18 0M12 11v4"/>',
        'layers' => '<path d="m12 3 10 5-10 5L2 8Zm-10 9 10 5 10-5M2 16l10 5 10-5"/>',
        'spark' => '<path d="m12 2 3 7 7 3-7 3-3 7-3-7-7-3 7-3Z"/>',
        'code' => '<path d="m7 6-6 6 6 6m10-12 6 6-6 6M14 3l-4 18"/>',
        'book' => '<path d="M12 5c-4-3-8-3-10-2v16c4-1 7 0 10 2 3-2 6-3 10-2V3c-2-1-6-1-10 2Zm0 0v16"/>',
        'chart' => '<path d="M4 21V12m8 9V3m8 18V8"/>',
        'compass' => '<circle cx="12" cy="12" r="10"/><path d="m16 8-3 5-5 3 3-5Z"/>',
        'type' => '<path d="M3 6V3h18v3M12 3v18M8 21h8"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><ellipse cx="12" cy="12" rx="4" ry="10"/><path d="M2 12h20"/>',
        'search' => '<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/>',
        'arrow' => '<path d="M5 19 19 5M5 5h14v14"/>',
        'check' => '<path d="m4 12 5 5L20 6"/>',
        'logout' => '<path d="M9 3H3v18h6m6-14 5 5-5 5M8 12h12"/>',
        'chevron' => '<path d="m6 9 6 6 6-6"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['grid']) . '</svg>';
}

function studio_render_field(string $name, array $field, array $record, string $prefix): void
{
    $value = (string) ($record[$name] ?? ($field['type'] === 'number' ? '99' : ''));
    $id = $prefix . '-' . $name;
    ?>
    <div class="studio-field <?= $field['type'] === 'textarea' ? 'field-wide' : '' ?>" data-field="<?= e($name) ?>">
        <label for="<?= e($id) ?>"><?= e($field['label']) ?><?php if ($field['required']): ?><span class="required-dot" aria-label="obligatorio">·</span><?php endif; ?></label>
        <?php if ($field['type'] === 'textarea'): ?>
            <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= str_contains($name, 'summary') ? '6' : '3' ?>" maxlength="<?= $field['max'] ?>" <?= $field['required'] ? 'required' : '' ?> aria-describedby="<?= e($id) ?>-help <?= e($id) ?>-error"><?= e($value) ?></textarea>
        <?php else: ?>
            <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="<?= in_array($field['type'], ['url', 'path'], true) ? 'text' : e($field['type']) ?>" value="<?= e($value) ?>" <?= $field['type'] === 'number' ? 'min="-9999" max="9999" step="1"' : 'maxlength="' . $field['max'] . '"' ?> <?= $field['required'] ? 'required' : '' ?> aria-describedby="<?= e($id) ?>-help <?= e($id) ?>-error" <?= in_array($field['type'], ['url', 'path', 'email'], true) ? 'spellcheck="false"' : '' ?>>
        <?php endif; ?>
        <small id="<?= e($id) ?>-help" class="field-help"><?= e($field['hint']) ?></small>
        <small id="<?= e($id) ?>-error" class="field-error"></small>
    </div>
    <?php
}

function studio_render_card(PDO $pdo, string $entity, array $record, string $csrf, bool $new = false, bool $open = false): void
{
    $config = studio_schema()[$entity];
    $id = (string) $record['id'];
    $prefix = $entity . '-' . $id;
    $title = $new ? 'Añadir ' . mb_strtolower($config['label']) : studio_title($entity, $record);
    $active = (int) ($record['is_active'] ?? 1);
    $hasVisibility = isset($config['fields']['is_active']);
    $canDelete = empty($config['singleton']) && $entity !== 'text';
    $subtitle = $entity === 'experience' ? ($record['title_es'] ?? '') : ($entity === 'text' ? ($record['value_es'] ?? '') : ($record['type_es'] ?? $record['period_es'] ?? $record['url'] ?? $config['label']));
    $fields = studio_fields($entity);
    $groups = ['base' => [], 'es' => [], 'en' => []];
    foreach ($fields as $key => $field) {
        if ($field['type'] === 'checkbox') continue;
        $group = $field['lang'] ?? ($entity === 'text' ? substr($key, -2) : 'base');
        $groups[$group][$key] = $field;
    }
    ?>
    <details class="editor-card <?= $new ? 'new-card' : '' ?>" id="<?= e($prefix) ?>" data-editor-card data-search="<?= e(mb_strtolower($title . ' ' . $subtitle . ' ' . $config['label'])) ?>" data-active="<?= $active ?>" <?= $open ? 'open' : '' ?>>
        <summary><span class="card-icon"><?= studio_icon($new ? 'spark' : studio_sections()[$config['section']]['icon']) ?></span><span class="card-summary"><strong data-card-title><?= e(mb_strimwidth($title, 0, 100, '…')) ?></strong><small data-card-subtitle><?= e(mb_strimwidth($subtitle, 0, 120, '…')) ?></small></span><?php if ($hasVisibility && !$new): ?><span class="visibility-badge <?= $active ? '' : 'is-hidden' ?>" data-visibility-badge><?= $active ? 'Publicado' : 'Oculto' ?></span><?php endif; ?><span class="card-dirty" aria-label="Cambios pendientes"></span><span class="card-chevron"><?= studio_icon('chevron') ?></span></summary>
        <div class="editor-body">
            <form method="post" action="/admin/save.php" data-editor-form data-entity="<?= e($entity) ?>" data-draft-key="<?= e($entity . ':' . ($new ? 'new:' . ($record[$config['parent']['field'] ?? ''] ?? '') : $id)) ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="entity" value="<?= e($entity) ?>">
                <input type="hidden" name="id" value="<?= e($id) ?>">
                <input type="hidden" name="revision" value="<?= $new ? 0 : studio_revision($pdo, $entity, $id) ?>">
                <input type="hidden" name="request_id" value="<?= e(bin2hex(random_bytes(16))) ?>">
                <input type="hidden" name="is_new" value="<?= $new ? '1' : '0' ?>">
                <?php if (isset($config['parent'])): ?><input type="hidden" name="<?= e($config['parent']['field']) ?>" value="<?= e($record[$config['parent']['field']]) ?>"><?php endif; ?>
                <div class="editor-toolbar">
                    <span class="form-status" data-form-status role="status"><?= $new ? 'Completa el nuevo bloque' : 'Sin cambios pendientes' ?></span>
                    <div><button class="studio-button small primary" type="submit" data-save><?= $new ? 'Crear bloque' : 'Guardar' ?></button><button class="icon-button" type="button" data-close-editor aria-label="Guardar y cerrar bloque">×</button></div>
                </div>
                <div class="form-error" data-form-error role="alert" hidden></div>
                <div class="recovery-actions" hidden><button type="button" class="text-button" data-retry>Volver a guardar</button><button type="button" class="text-button" data-discard>Descartar borrador local</button><a href="/admin/" target="_blank" rel="noopener" data-login hidden>Iniciar sesión ↗</a></div>
                <?php if ($hasVisibility): ?><label class="visibility-control"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>><span class="switch-track"></span><span><strong>Visible en el portfolio</strong><small>Al ocultarlo, conservas el contenido en este panel.</small></span></label><?php endif; ?>
                <?php if ($groups['base']): ?><div class="field-grid"><?php foreach ($groups['base'] as $key => $field) studio_render_field($key, $field, $record, $prefix); ?></div><?php endif; ?>
                <?php if ($groups['es']): ?>
                    <div class="language-tabs" role="tablist" aria-label="Idioma del contenido"><button type="button" role="tab" aria-selected="true" aria-controls="<?= e($prefix) ?>-es" id="<?= e($prefix) ?>-tab-es" data-editor-lang="es">ES <span>Español</span></button><button type="button" role="tab" aria-selected="false" aria-controls="<?= e($prefix) ?>-en" id="<?= e($prefix) ?>-tab-en" data-editor-lang="en">EN <span>English</span></button><small>Si el inglés queda vacío, se usa el español.</small></div>
                    <?php foreach (['es', 'en'] as $lang): ?><div class="field-grid language-panel" role="tabpanel" id="<?= e($prefix) ?>-<?= $lang ?>" aria-labelledby="<?= e($prefix) ?>-tab-<?= $lang ?>" data-language-panel="<?= $lang ?>"><?php foreach ($groups[$lang] as $key => $field) studio_render_field($key, $field, $record, $prefix); ?></div><?php endforeach; ?>
                <?php endif; ?>
                <div class="editor-footer"><span><?= $new ? 'Se creará cuando los campos obligatorios estén completos.' : 'Guardado automático al editar y al cerrar.' ?></span><div><?php if ($canDelete): ?><button class="text-button danger" type="submit" name="operation" value="delete" data-delete <?= $new ? 'hidden' : '' ?>>Eliminar</button><?php endif; ?><button class="text-button" type="button" data-close-editor>Guardar y cerrar ↗</button></div></div>
                <?php if ($new): ?><a class="studio-button create-children" href="/admin/content.php?section=<?= e($config['section']) ?><?= isset($config['parent']) ? '&open=' . e($record[$config['parent']['field']]) : '#newRecord' ?>" data-add-another hidden>+ Añadir otro bloque</a><?php endif; ?>
                <?php if ($new && in_array($entity, ['experience', 'hero_card'], true)): ?><a class="studio-button create-children" href="/admin/content.php?section=<?= e($config['section']) ?>&open=<?= e($id) ?>" data-created-link hidden>Gestionar <?= $entity === 'experience' ? 'proyectos de esta etapa' : 'enlaces de esta especialidad' ?> ↗</a><?php endif; ?>
            </form>
            <?php if (!$new && in_array($entity, ['experience', 'hero_card'], true)): ?>
                <div class="nested-editors"><div class="nested-heading"><h3><?= $entity === 'experience' ? 'Proyectos clave de esta etapa' : 'Enlaces de la especialidad' ?></h3><p>Cada enlace se guarda de forma independiente.</p></div>
                    <?php $child = $entity === 'experience' ? 'exp_project' : 'hero_item'; $parentField = studio_schema()[$child]['parent']['field']; ?>
                    <?php foreach (studio_records($pdo, $child, $id) as $row) studio_render_card($pdo, $child, $row, $csrf); ?>
                    <?php studio_render_card($pdo, $child, ['id' => app_uid(), $parentField => $id], $csrf, true); ?>
                </div>
            <?php endif; ?>
        </div>
    </details>
    <?php
}
