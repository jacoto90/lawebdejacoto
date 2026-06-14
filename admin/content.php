<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$pdo = app_db();
app_run_admin_migrations($pdo);
app_run_portfolio_migrations($pdo);
app_seed_portfolio_content($pdo);

$currentUser = admin_current_user($pdo);
if (!$currentUser) {
    header('Location: /admin/');
    exit;
}

$sections = ['profile', 'texts', 'skills', 'highlights', 'studies', 'services', 'projects', 'experience', 'hero'];
$section = (string) ($_GET['section'] ?? 'profile');
if (!in_array($section, $sections, true)) {
    $section = 'profile';
}

$errors = [];

function admin_post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function admin_int(string $key, int $default = 0): int
{
    return (int) ($_POST[$key] ?? $default);
}

function admin_bool(string $key): int
{
    return isset($_POST[$key]) ? 1 : 0;
}

function admin_lang_value(string $field, string $lang): string
{
    return admin_post($field . '_' . $lang);
}

function admin_en_or_es(string $en, string $es): string
{
    return $en !== '' ? $en : $es;
}

function admin_redirect(string $section): void
{
    header('Location: /admin/content.php?section=' . rawurlencode($section) . '&saved=1');
    exit;
}

function admin_delete_by_id(PDO $pdo, string $table, string $field, string $id): void
{
    $stmt = $pdo->prepare('DELETE FROM ' . $table . ' WHERE ' . $field . ' = :id');
    $stmt->execute(['id' => $id]);
}

function admin_experience_tags(PDO $pdo, string $experienceId): string
{
    $stmt = $pdo->prepare('SELECT name FROM portfolio_experience_tags WHERE experience_id = :id ORDER BY sort_order ASC');
    $stmt->execute(['id' => $experienceId]);
    return implode(', ', array_column($stmt->fetchAll(), 'name'));
}

function admin_save_experience_tags(PDO $pdo, string $experienceId, string $csv): void
{
    admin_delete_by_id($pdo, 'portfolio_experience_tags', 'experience_id', $experienceId);
    $tags = array_values(array_filter(array_map('trim', explode(',', $csv)), static fn($tag) => $tag !== ''));
    foreach ($tags as $index => $tag) {
        app_upsert($pdo, 'portfolio_experience_tags', ['id'], ['id' => app_uid(), 'experience_id' => $experienceId, 'name' => $tag, 'sort_order' => $index]);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!app_verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Token de seguridad inválido.';
    } else {
        $action = admin_post('action');

        if ($action === 'save_profile') {
            app_upsert($pdo, 'portfolio_profile', ['id'], [
                'id' => 'main',
                'name' => admin_post('name'),
                'email' => admin_post('email'),
                'phone' => admin_post('phone'),
                'linkedin' => admin_post('linkedin'),
                'cv' => admin_post('cv'),
                'cv_download_name' => admin_post('cv_download_name'),
                'photo' => admin_post('photo'),
                'cv_photo' => admin_post('cv_photo') ?: 'images/perfil_joseangel_linkedin.jpg',
                'golf_photo' => admin_post('golf_photo'),
                'updated_at' => app_now(),
            ]);
            foreach (['es', 'en'] as $lang) {
                app_upsert($pdo, 'portfolio_profile_i18n', ['profile_id', 'lang'], [
                    'profile_id' => 'main',
                    'lang' => $lang,
                    'role' => admin_lang_value('role', $lang),
                    'location' => admin_lang_value('location', $lang),
                    'age' => admin_lang_value('age', $lang),
                    'bio' => admin_lang_value('bio', $lang),
                    'hobby_title' => admin_lang_value('hobby_title', $lang),
                    'hobby_text' => admin_lang_value('hobby_text', $lang),
                ]);
            }
            admin_redirect('profile');
        }

        if ($action === 'save_texts') {
            foreach ($_POST['texts'] ?? [] as $key => $langs) {
                if (!is_array($langs)) {
                    continue;
                }
                $es = trim((string) ($langs['es'] ?? ''));
                $en = admin_en_or_es(trim((string) ($langs['en'] ?? '')), $es);
                app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => (string) $key, 'lang' => 'es', 'text_value' => $es]);
                app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => (string) $key, 'lang' => 'en', 'text_value' => $en]);
            }
            admin_redirect('texts');
        }

        if ($action === 'save_skill' || $action === 'add_skill') {
            $id = $action === 'add_skill' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_skills', ['id'], ['id' => $id, 'name' => admin_post('name'), 'years_label' => admin_post('years_label'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            admin_redirect('skills');
        }
        if ($action === 'delete_skill') {
            admin_delete_by_id($pdo, 'portfolio_skills', 'id', admin_post('id'));
            admin_redirect('skills');
        }

        if ($action === 'save_highlight' || $action === 'add_highlight') {
            $id = $action === 'add_highlight' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_highlights', ['id'], ['id' => $id, 'metric' => admin_post('metric'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            $labelEs = admin_lang_value('label', 'es');
            $labelEn = admin_en_or_es(admin_lang_value('label', 'en'), $labelEs);
            app_upsert($pdo, 'portfolio_highlight_i18n', ['highlight_id', 'lang'], ['highlight_id' => $id, 'lang' => 'es', 'label' => $labelEs]);
            app_upsert($pdo, 'portfolio_highlight_i18n', ['highlight_id', 'lang'], ['highlight_id' => $id, 'lang' => 'en', 'label' => $labelEn]);
            admin_redirect('highlights');
        }
        if ($action === 'delete_highlight') {
            $id = admin_post('id');
            admin_delete_by_id($pdo, 'portfolio_highlight_i18n', 'highlight_id', $id);
            admin_delete_by_id($pdo, 'portfolio_highlights', 'id', $id);
            admin_redirect('highlights');
        }

        if ($action === 'save_study' || $action === 'add_study') {
            $id = $action === 'add_study' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_studies', ['id'], ['id' => $id, 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            foreach (['es', 'en'] as $lang) {
                $periodEs = admin_lang_value('period', 'es');
                $titleEs = admin_lang_value('title', 'es');
                $centerEs = admin_lang_value('center', 'es');
                app_upsert($pdo, 'portfolio_study_i18n', ['study_id', 'lang'], [
                    'study_id' => $id,
                    'lang' => $lang,
                    'period' => $lang === 'en' ? admin_en_or_es(admin_lang_value('period', 'en'), $periodEs) : $periodEs,
                    'title' => $lang === 'en' ? admin_en_or_es(admin_lang_value('title', 'en'), $titleEs) : $titleEs,
                    'center' => $lang === 'en' ? admin_en_or_es(admin_lang_value('center', 'en'), $centerEs) : $centerEs,
                ]);
            }
            admin_redirect('studies');
        }
        if ($action === 'delete_study') {
            $id = admin_post('id');
            admin_delete_by_id($pdo, 'portfolio_study_i18n', 'study_id', $id);
            admin_delete_by_id($pdo, 'portfolio_studies', 'id', $id);
            admin_redirect('studies');
        }

        if ($action === 'save_service' || $action === 'add_service') {
            $id = $action === 'add_service' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_services', ['id'], ['id' => $id, 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            $titleEs = admin_lang_value('title', 'es');
            $descEs = admin_lang_value('description', 'es');
            app_upsert($pdo, 'portfolio_service_i18n', ['service_id', 'lang'], ['service_id' => $id, 'lang' => 'es', 'title' => $titleEs, 'description' => $descEs]);
            app_upsert($pdo, 'portfolio_service_i18n', ['service_id', 'lang'], ['service_id' => $id, 'lang' => 'en', 'title' => admin_en_or_es(admin_lang_value('title', 'en'), $titleEs), 'description' => admin_en_or_es(admin_lang_value('description', 'en'), $descEs)]);
            admin_redirect('services');
        }
        if ($action === 'delete_service') {
            $id = admin_post('id');
            admin_delete_by_id($pdo, 'portfolio_service_i18n', 'service_id', $id);
            admin_delete_by_id($pdo, 'portfolio_services', 'id', $id);
            admin_redirect('services');
        }

        if ($action === 'save_project' || $action === 'add_project') {
            $id = $action === 'add_project' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_projects', ['id'], ['id' => $id, 'name' => admin_post('name'), 'logo' => admin_post('logo', 'PR'), 'url' => admin_post('url'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            $typeEs = admin_lang_value('type', 'es');
            $descEs = admin_lang_value('description', 'es');
            app_upsert($pdo, 'portfolio_project_i18n', ['project_id', 'lang'], ['project_id' => $id, 'lang' => 'es', 'type' => $typeEs, 'description' => $descEs]);
            app_upsert($pdo, 'portfolio_project_i18n', ['project_id', 'lang'], ['project_id' => $id, 'lang' => 'en', 'type' => admin_en_or_es(admin_lang_value('type', 'en'), $typeEs), 'description' => admin_en_or_es(admin_lang_value('description', 'en'), $descEs)]);
            admin_redirect('projects');
        }
        if ($action === 'delete_project') {
            $id = admin_post('id');
            admin_delete_by_id($pdo, 'portfolio_project_i18n', 'project_id', $id);
            admin_delete_by_id($pdo, 'portfolio_projects', 'id', $id);
            admin_redirect('projects');
        }

        if ($action === 'save_experience' || $action === 'add_experience') {
            $id = $action === 'add_experience' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_experiences', ['id'], ['id' => $id, 'company' => admin_post('company'), 'start_date' => admin_post('start_date'), 'end_date' => admin_post('end_date'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            $periodEs = admin_lang_value('period', 'es');
            $titleEs = admin_lang_value('title', 'es');
            $summaryEs = admin_lang_value('summary', 'es');
            app_upsert($pdo, 'portfolio_experience_i18n', ['experience_id', 'lang'], ['experience_id' => $id, 'lang' => 'es', 'period' => $periodEs, 'title' => $titleEs, 'summary' => $summaryEs]);
            app_upsert($pdo, 'portfolio_experience_i18n', ['experience_id', 'lang'], ['experience_id' => $id, 'lang' => 'en', 'period' => admin_en_or_es(admin_lang_value('period', 'en'), $periodEs), 'title' => admin_en_or_es(admin_lang_value('title', 'en'), $titleEs), 'summary' => admin_en_or_es(admin_lang_value('summary', 'en'), $summaryEs)]);
            admin_save_experience_tags($pdo, $id, admin_post('tags'));
            admin_redirect('experience');
        }
        if ($action === 'delete_experience') {
            $id = admin_post('id');
            $projectStmt = $pdo->prepare('SELECT id FROM portfolio_experience_projects WHERE experience_id = :id');
            $projectStmt->execute(['id' => $id]);
            foreach ($projectStmt->fetchAll() as $project) {
                admin_delete_by_id($pdo, 'portfolio_experience_project_i18n', 'project_id', $project['id']);
            }
            admin_delete_by_id($pdo, 'portfolio_experience_projects', 'experience_id', $id);
            admin_delete_by_id($pdo, 'portfolio_experience_tags', 'experience_id', $id);
            admin_delete_by_id($pdo, 'portfolio_experience_i18n', 'experience_id', $id);
            admin_delete_by_id($pdo, 'portfolio_experiences', 'id', $id);
            admin_redirect('experience');
        }

        if ($action === 'save_exp_project' || $action === 'add_exp_project') {
            $id = $action === 'add_exp_project' ? app_uid() : admin_post('id');
            $experienceId = admin_post('experience_id');
            app_upsert($pdo, 'portfolio_experience_projects', ['id'], ['id' => $id, 'experience_id' => $experienceId, 'url' => admin_post('url'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            $textEs = admin_lang_value('text_value', 'es');
            $labelEs = admin_lang_value('url_label', 'es');
            app_upsert($pdo, 'portfolio_experience_project_i18n', ['project_id', 'lang'], ['project_id' => $id, 'lang' => 'es', 'text_value' => $textEs, 'url_label' => $labelEs]);
            app_upsert($pdo, 'portfolio_experience_project_i18n', ['project_id', 'lang'], ['project_id' => $id, 'lang' => 'en', 'text_value' => admin_en_or_es(admin_lang_value('text_value', 'en'), $textEs), 'url_label' => admin_en_or_es(admin_lang_value('url_label', 'en'), $labelEs)]);
            admin_redirect('experience');
        }
        if ($action === 'delete_exp_project') {
            $id = admin_post('id');
            admin_delete_by_id($pdo, 'portfolio_experience_project_i18n', 'project_id', $id);
            admin_delete_by_id($pdo, 'portfolio_experience_projects', 'id', $id);
            admin_redirect('experience');
        }

        if ($action === 'save_hero_texts') {
            $es = trim((string) ($_POST['hero_typewriter_es'] ?? ''));
            $en = admin_en_or_es(trim((string) ($_POST['hero_typewriter_en'] ?? '')), $es);
            app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => 'hero_typewriter', 'lang' => 'es', 'text_value' => $es]);
            app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => 'hero_typewriter', 'lang' => 'en', 'text_value' => $en]);
            admin_redirect('hero');
        }

        if ($action === 'save_hero_card' || $action === 'add_hero_card') {
            $id = $action === 'add_hero_card' ? app_uid() : admin_post('id');
            app_upsert($pdo, 'portfolio_hero_cards', ['id'], ['id' => $id, 'name' => admin_post('name'), 'label' => admin_post('label'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            admin_redirect('hero');
        }
        if ($action === 'delete_hero_card') {
            $id = admin_post('id');
            admin_delete_by_id($pdo, 'portfolio_hero_card_items', 'card_id', $id);
            admin_delete_by_id($pdo, 'portfolio_hero_cards', 'id', $id);
            admin_redirect('hero');
        }

        if ($action === 'save_hero_item' || $action === 'add_hero_item') {
            $id = $action === 'add_hero_item' ? app_uid() : admin_post('id');
            $cardId = admin_post('card_id');
            app_upsert($pdo, 'portfolio_hero_card_items', ['id'], ['id' => $id, 'card_id' => $cardId, 'label' => admin_post('label'), 'url' => admin_post('url'), 'sort_order' => admin_int('sort_order'), 'is_active' => admin_bool('is_active')]);
            admin_redirect('hero');
        }
        if ($action === 'delete_hero_item') {
            admin_delete_by_id($pdo, 'portfolio_hero_card_items', 'id', admin_post('id'));
            admin_redirect('hero');
        }
    }
}

$profile = app_portfolio_profile_from_db($pdo);
$translations = app_portfolio_translations_from_db($pdo, require APP_BASE_PATH . '/data/translations.php');
$csrf = app_csrf_token();

function admin_i18n(PDO $pdo, string $table, string $field, string $id): array
{
    $stmt = $pdo->prepare('SELECT * FROM ' . $table . ' WHERE ' . $field . ' = :id');
    $stmt->execute(['id' => $id]);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[$row['lang']] = $row;
    }
    return $out;
}

function admin_rows(PDO $pdo, string $sql): array
{
    return $pdo->query($sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Portfolio · JACOTO</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="admin-page">
<main class="admin-editor-shell">
    <div class="admin-editor-head">
        <div>
            <p class="eyebrow">Panel privado</p>
            <h1>Editar portfolio</h1>
        </div>
        <div class="admin-editor-actions">
            <a class="btn btn-ghost" href="/">Ver web</a>
            <a class="btn btn-ghost" href="/admin/">Panel</a>
        </div>
    </div>

    <nav class="admin-tabs">
        <?php foreach ($sections as $item): ?>
            <a class="<?= $section === $item ? 'active' : '' ?>" href="/admin/content.php?section=<?= e($item) ?>"><?= e(ucfirst($item)) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if (isset($_GET['saved'])): ?>
        <div class="admin-alert admin-alert-success">Cambios guardados.</div>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <div class="admin-alert admin-alert-error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if ($section === 'profile'): ?>
        <?php $stmt = $pdo->query("SELECT * FROM portfolio_profile WHERE id = 'main'"); $base = $stmt->fetch(); $i18n = admin_i18n($pdo, 'portfolio_profile_i18n', 'profile_id', 'main'); ?>
        <form class="admin-editor-card admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_profile">
            <h2>Perfil, hero y hobby</h2>
            <div class="admin-grid-two">
                <?php foreach (['name' => 'Nombre', 'email' => 'Email', 'phone' => 'Teléfono', 'linkedin' => 'LinkedIn', 'cv' => 'Ruta CV', 'cv_download_name' => 'Nombre descarga CV', 'photo' => 'Foto perfil', 'cv_photo' => 'Foto CV / LinkedIn', 'golf_photo' => 'Foto golf'] as $field => $label): ?>
                    <label><?= e($label) ?><input name="<?= e($field) ?>" value="<?= e((string) ($base[$field] ?? '')) ?>" required></label>
                <?php endforeach; ?>
            </div>
            <?php foreach (['es' => 'Español', 'en' => 'Inglés'] as $lang => $label): ?>
                <h3><?= e($label) ?></h3>
                <div class="admin-grid-two">
                    <label>Rol<input name="role_<?= e($lang) ?>" value="<?= e((string) ($i18n[$lang]['role'] ?? '')) ?>"></label>
                    <label>Ubicación<input name="location_<?= e($lang) ?>" value="<?= e((string) ($i18n[$lang]['location'] ?? '')) ?>"></label>
                    <label>Edad<input name="age_<?= e($lang) ?>" value="<?= e((string) ($i18n[$lang]['age'] ?? '')) ?>"></label>
                    <label>Título hobby<input name="hobby_title_<?= e($lang) ?>" value="<?= e((string) ($i18n[$lang]['hobby_title'] ?? '')) ?>"></label>
                </div>
                <label>Bio<textarea name="bio_<?= e($lang) ?>" rows="3"><?= e((string) ($i18n[$lang]['bio'] ?? '')) ?></textarea></label>
                <label>Texto hobby<textarea name="hobby_text_<?= e($lang) ?>" rows="3"><?= e((string) ($i18n[$lang]['hobby_text'] ?? '')) ?></textarea></label>
            <?php endforeach; ?>
            <button class="btn btn-primary" type="submit">Guardar perfil</button>
        </form>
    <?php endif; ?>

    <?php if ($section === 'texts'): ?>
        <form class="admin-editor-card admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_texts">
            <h2>Textos generales y SEO</h2>
            <?php foreach (array_keys($translations['es']) as $key): ?>
                <div class="admin-edit-row">
                    <h3><?= e($key) ?></h3>
                    <label>ES<textarea name="texts[<?= e($key) ?>][es]" rows="2"><?= e((string) ($translations['es'][$key] ?? '')) ?></textarea></label>
                    <label>EN<textarea name="texts[<?= e($key) ?>][en]" rows="2"><?= e((string) ($translations['en'][$key] ?? '')) ?></textarea></label>
                </div>
            <?php endforeach; ?>
            <button class="btn btn-primary" type="submit">Guardar textos</button>
        </form>
    <?php endif; ?>

    <?php if ($section === 'skills'): ?>
        <section class="admin-editor-card"><h2>Skills</h2><?php foreach (admin_rows($pdo, 'SELECT * FROM portfolio_skills ORDER BY sort_order ASC, name ASC') as $row): ?>
            <form class="admin-inline-form admin-inline-form-skills" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><input type="hidden" name="action" value="save_skill"><input name="name" value="<?= e($row['name']) ?>" placeholder="Skill"><input name="years_label" value="<?= e((string) ($row['years_label'] ?? '')) ?>" placeholder="Años"><input name="sort_order" type="number" value="<?= e((string) $row['sort_order']) ?>"><label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $row['is_active'] === 1 ? 'checked' : '' ?>> activo</label><button class="btn btn-primary">Guardar</button><button class="btn btn-ghost" name="action" value="delete_skill" onclick="return confirm('Eliminar skill?')">Eliminar</button></form>
        <?php endforeach; ?><form class="admin-inline-form admin-inline-form-skills admin-add-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="add_skill"><input name="name" placeholder="Nueva skill"><input name="years_label" placeholder="Ej: 2 años"><input name="sort_order" type="number" value="99"><label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label><button class="btn btn-primary">Añadir</button></form></section>
    <?php endif; ?>

    <?php if ($section === 'highlights'): ?>
        <section class="admin-editor-card"><h2>Métricas hero</h2><?php foreach (admin_rows($pdo, 'SELECT * FROM portfolio_highlights ORDER BY sort_order ASC') as $row): $i18n = admin_i18n($pdo, 'portfolio_highlight_i18n', 'highlight_id', $row['id']); ?>
            <form class="admin-edit-row admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><input type="hidden" name="action" value="save_highlight"><div class="admin-grid-two"><label>Métrica<input name="metric" value="<?= e($row['metric']) ?>"></label><label>Orden<input name="sort_order" type="number" value="<?= e((string) $row['sort_order']) ?>"></label><label>Label ES<input name="label_es" value="<?= e((string) ($i18n['es']['label'] ?? '')) ?>"></label><label>Label EN<input name="label_en" value="<?= e((string) ($i18n['en']['label'] ?? '')) ?>"></label></div><label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $row['is_active'] === 1 ? 'checked' : '' ?>> activo</label><button class="btn btn-primary">Guardar</button><button class="btn btn-ghost" name="action" value="delete_highlight" onclick="return confirm('Eliminar métrica?')">Eliminar</button></form>
        <?php endforeach; ?><form class="admin-edit-row admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="add_highlight"><div class="admin-grid-two"><label>Métrica<input name="metric"></label><label>Orden<input name="sort_order" type="number" value="99"></label><label>Label ES<input name="label_es"></label><label>Label EN<input name="label_en"></label></div><label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label><button class="btn btn-primary">Añadir métrica</button></form></section>
    <?php endif; ?>

    <?php if ($section === 'studies' || $section === 'services' || $section === 'projects'): ?>
        <?php
        $config = [
            'studies' => ['title' => 'Estudios', 'table' => 'portfolio_studies', 'i18n' => 'portfolio_study_i18n', 'id' => 'study_id', 'fields' => ['period' => 'Periodo', 'title' => 'Título', 'center' => 'Centro'], 'base' => []],
            'services' => ['title' => 'Servicios', 'table' => 'portfolio_services', 'i18n' => 'portfolio_service_i18n', 'id' => 'service_id', 'fields' => ['title' => 'Título', 'description' => 'Descripción'], 'base' => []],
            'projects' => ['title' => 'Proyectos destacados', 'table' => 'portfolio_projects', 'i18n' => 'portfolio_project_i18n', 'id' => 'project_id', 'fields' => ['type' => 'Tipo', 'description' => 'Descripción'], 'base' => ['name' => 'Nombre', 'logo' => 'Logo', 'url' => 'URL']],
        ][$section];
        $actionBase = ['studies' => 'study', 'services' => 'service', 'projects' => 'project'][$section];
        ?>
        <section class="admin-editor-card"><h2><?= e($config['title']) ?></h2><?php foreach (admin_rows($pdo, 'SELECT * FROM ' . $config['table'] . ' ORDER BY sort_order ASC') as $row): $i18n = admin_i18n($pdo, $config['i18n'], $config['id'], $row['id']); ?>
            <form class="admin-edit-row admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><input type="hidden" name="action" value="save_<?= e($actionBase) ?>"><div class="admin-grid-two"><?php foreach ($config['base'] as $field => $label): ?><label><?= e($label) ?><input name="<?= e($field) ?>" value="<?= e((string) ($row[$field] ?? '')) ?>"></label><?php endforeach; ?><label>Orden<input name="sort_order" type="number" value="<?= e((string) $row['sort_order']) ?>"></label><?php foreach ($config['fields'] as $field => $label): ?><label><?= e($label) ?> ES<textarea name="<?= e($field) ?>_es" rows="2"><?= e((string) ($i18n['es'][$field] ?? '')) ?></textarea></label><label><?= e($label) ?> EN<textarea name="<?= e($field) ?>_en" rows="2"><?= e((string) ($i18n['en'][$field] ?? '')) ?></textarea></label><?php endforeach; ?></div><label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $row['is_active'] === 1 ? 'checked' : '' ?>> activo</label><button class="btn btn-primary">Guardar</button><button class="btn btn-ghost" name="action" value="delete_<?= e($actionBase) ?>" onclick="return confirm('Eliminar elemento?')">Eliminar</button></form>
        <?php endforeach; ?><form class="admin-edit-row admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="add_<?= e($actionBase) ?>"><div class="admin-grid-two"><?php foreach ($config['base'] as $field => $label): ?><label><?= e($label) ?><input name="<?= e($field) ?>"></label><?php endforeach; ?><label>Orden<input name="sort_order" type="number" value="99"></label><?php foreach ($config['fields'] as $field => $label): ?><label><?= e($label) ?> ES<textarea name="<?= e($field) ?>_es" rows="2"></textarea></label><label><?= e($label) ?> EN<textarea name="<?= e($field) ?>_en" rows="2"></textarea></label><?php endforeach; ?></div><label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label><button class="btn btn-primary">Añadir</button></form></section>
    <?php endif; ?>

    <?php if ($section === 'experience'): ?>
        <section class="admin-editor-card"><h2>Experiencia</h2><?php foreach (admin_rows($pdo, 'SELECT * FROM portfolio_experiences ORDER BY sort_order ASC') as $row): $i18n = admin_i18n($pdo, 'portfolio_experience_i18n', 'experience_id', $row['id']); ?>
            <article class="admin-edit-row"><form class="admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($row['id']) ?>"><input type="hidden" name="action" value="save_experience"><h3><?= e($row['company']) ?></h3><div class="admin-grid-two"><label>Empresa<input name="company" value="<?= e($row['company']) ?>"></label><label>Orden<input name="sort_order" type="number" value="<?= e((string) $row['sort_order']) ?>"></label><label>Fecha inicio<input name="start_date" type="date" value="<?= e((string) ($row['start_date'] ?? '')) ?>"></label><label>Fecha fin <small>(vacío = actualidad)</small><input name="end_date" type="date" value="<?= e((string) ($row['end_date'] ?? '')) ?>"></label><label>Tags separados por coma<input name="tags" value="<?= e(admin_experience_tags($pdo, $row['id'])) ?>"></label><label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $row['is_active'] === 1 ? 'checked' : '' ?>> activo</label><label>Periodo ES<input name="period_es" value="<?= e((string) ($i18n['es']['period'] ?? '')) ?>"></label><label>Periodo EN<input name="period_en" value="<?= e((string) ($i18n['en']['period'] ?? '')) ?>"></label><label>Título ES<input name="title_es" value="<?= e((string) ($i18n['es']['title'] ?? '')) ?>"></label><label>Título EN<input name="title_en" value="<?= e((string) ($i18n['en']['title'] ?? '')) ?>"></label><label>Resumen ES<textarea name="summary_es" rows="3"><?= e((string) ($i18n['es']['summary'] ?? '')) ?></textarea></label><label>Resumen EN<textarea name="summary_en" rows="3"><?= e((string) ($i18n['en']['summary'] ?? '')) ?></textarea></label></div><button class="btn btn-primary">Guardar experiencia</button><button class="btn btn-ghost" name="action" value="delete_experience" onclick="return confirm('Eliminar experiencia completa?')">Eliminar</button></form>
            <div class="admin-nested"><h4>Proyectos clave</h4><?php $projects = $pdo->prepare('SELECT * FROM portfolio_experience_projects WHERE experience_id = :id ORDER BY sort_order ASC'); $projects->execute(['id' => $row['id']]); foreach ($projects->fetchAll() as $project): $pi18n = admin_i18n($pdo, 'portfolio_experience_project_i18n', 'project_id', $project['id']); ?><form class="admin-form admin-nested-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($project['id']) ?>"><input type="hidden" name="experience_id" value="<?= e($row['id']) ?>"><input type="hidden" name="action" value="save_exp_project"><div class="admin-grid-two"><label>Texto ES<textarea name="text_value_es" rows="2"><?= e((string) ($pi18n['es']['text_value'] ?? '')) ?></textarea></label><label>Texto EN<textarea name="text_value_en" rows="2"><?= e((string) ($pi18n['en']['text_value'] ?? '')) ?></textarea></label><label>URL<input name="url" value="<?= e($project['url']) ?>"></label><label>Orden<input name="sort_order" type="number" value="<?= e((string) $project['sort_order']) ?>"></label><label>Label URL ES<input name="url_label_es" value="<?= e((string) ($pi18n['es']['url_label'] ?? '')) ?>"></label><label>Label URL EN<input name="url_label_en" value="<?= e((string) ($pi18n['en']['url_label'] ?? '')) ?>"></label></div><label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $project['is_active'] === 1 ? 'checked' : '' ?>> activo</label><button class="btn btn-primary">Guardar proyecto</button><button class="btn btn-ghost" name="action" value="delete_exp_project" onclick="return confirm('Eliminar proyecto clave?')">Eliminar</button></form><?php endforeach; ?><form class="admin-form admin-nested-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="experience_id" value="<?= e($row['id']) ?>"><input type="hidden" name="action" value="add_exp_project"><div class="admin-grid-two"><label>Texto ES<textarea name="text_value_es" rows="2"></textarea></label><label>Texto EN<textarea name="text_value_en" rows="2"></textarea></label><label>URL<input name="url"></label><label>Orden<input name="sort_order" type="number" value="99"></label><label>Label URL ES<input name="url_label_es"></label><label>Label URL EN<input name="url_label_en"></label></div><label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label><button class="btn btn-primary">Añadir proyecto clave</button></form></div></article>
        <?php endforeach; ?><form class="admin-edit-row admin-form" method="post"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="add_experience"><h3>Nueva experiencia</h3><div class="admin-grid-two"><label>Empresa<input name="company"></label><label>Orden<input name="sort_order" type="number" value="99"></label><label>Fecha inicio<input name="start_date" type="date"></label><label>Fecha fin <small>(vacío = actualidad)</small><input name="end_date" type="date"></label><label>Tags<input name="tags"></label><label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label><label>Periodo ES<input name="period_es"></label><label>Periodo EN<input name="period_en"></label><label>Título ES<input name="title_es"></label><label>Título EN<input name="title_en"></label><label>Resumen ES<textarea name="summary_es" rows="3"></textarea></label><label>Resumen EN<textarea name="summary_en" rows="3"></textarea></label></div><button class="btn btn-primary">Añadir experiencia</button></form></section>
    <?php endif; ?>

    <?php if ($section === 'hero'): ?>
        <?php $heroTypewriterEs = $pdo->prepare("SELECT text_value FROM portfolio_texts WHERE text_key = 'hero_typewriter' AND lang = 'es'"); $heroTypewriterEs->execute(); $heroTypewriterEsVal = (string) $heroTypewriterEs->fetchColumn(); ?>
        <?php $heroTypewriterEn = $pdo->prepare("SELECT text_value FROM portfolio_texts WHERE text_key = 'hero_typewriter' AND lang = 'en'"); $heroTypewriterEn->execute(); $heroTypewriterEnVal = (string) $heroTypewriterEn->fetchColumn(); ?>
        <form class="admin-editor-card admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="save_hero_texts">
            <h2>Texto de presentación hero</h2>
            <div class="admin-grid-two">
                <label>ES <small>(máquina de escribir)</small><textarea name="hero_typewriter_es" rows="3"><?= e($heroTypewriterEsVal) ?></textarea></label>
                <label>EN <small>(typewriter)</small><textarea name="hero_typewriter_en" rows="3"><?= e($heroTypewriterEnVal) ?></textarea></label>
            </div>
            <button class="btn btn-primary">Guardar texto</button>
        </form>

        <section class="admin-editor-card"><h2>Chips del hero</h2>
        <?php foreach (admin_rows($pdo, 'SELECT * FROM portfolio_hero_cards ORDER BY sort_order ASC') as $row): ?>
            <article class="admin-edit-row">
                <form class="admin-form" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                    <input type="hidden" name="action" value="save_hero_card">
                    <h3><?= e($row['label']) ?></h3>
                    <div class="admin-grid-two">
                        <label>Nombre interno<input name="name" value="<?= e($row['name']) ?>"></label>
                        <label>Etiqueta visible<input name="label" value="<?= e($row['label']) ?>"></label>
                        <label>Orden<input name="sort_order" type="number" value="<?= e((string) $row['sort_order']) ?>"></label>
                        <label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $row['is_active'] === 1 ? 'checked' : '' ?>> activo</label>
                    </div>
                    <button class="btn btn-primary">Guardar chip</button>
                    <button class="btn btn-ghost" name="action" value="delete_hero_card" onclick="return confirm('Eliminar chip y sus subchips?')">Eliminar</button>
                </form>
                <div class="admin-nested"><h4>Subchips</h4>
                <?php $items = $pdo->prepare('SELECT * FROM portfolio_hero_card_items WHERE card_id = :id ORDER BY sort_order ASC'); $items->execute(['id' => $row['id']]); foreach ($items->fetchAll() as $item): ?>
                    <form class="admin-form admin-nested-form" method="post">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                        <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                        <input type="hidden" name="card_id" value="<?= e($row['id']) ?>">
                        <input type="hidden" name="action" value="save_hero_item">
                        <div class="admin-grid-two">
                            <label>Texto<input name="label" value="<?= e($item['label']) ?>"></label>
                            <label>URL <small>(#seccion, https://... o ruta interna)</small><input name="url" value="<?= e($item['url']) ?>"></label>
                            <label>Orden<input name="sort_order" type="number" value="<?= e((string) $item['sort_order']) ?>"></label>
                            <label class="admin-check"><input type="checkbox" name="is_active" <?= (int) $item['is_active'] === 1 ? 'checked' : '' ?>> activo</label>
                        </div>
                        <button class="btn btn-primary">Guardar subchip</button>
                        <button class="btn btn-ghost" name="action" value="delete_hero_item" onclick="return confirm('Eliminar subchip?')">Eliminar</button>
                    </form>
                <?php endforeach; ?>
                    <form class="admin-form admin-nested-form" method="post">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                        <input type="hidden" name="card_id" value="<?= e($row['id']) ?>">
                        <input type="hidden" name="action" value="add_hero_item">
                        <div class="admin-grid-two">
                            <label>Texto<input name="label"></label>
                            <label>URL<input name="url" placeholder="#projects"></label>
                            <label>Orden<input name="sort_order" type="number" value="99"></label>
                            <label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label>
                        </div>
                        <button class="btn btn-primary">Añadir subchip</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
            <form class="admin-edit-row admin-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="action" value="add_hero_card">
                <h3>Nuevo chip</h3>
                <div class="admin-grid-two">
                    <label>Nombre interno<input name="name"></label>
                    <label>Etiqueta visible<input name="label"></label>
                    <label>Orden<input name="sort_order" type="number" value="99"></label>
                    <label class="admin-check"><input type="checkbox" name="is_active" checked> activo</label>
                </div>
                <button class="btn btn-primary">Añadir chip</button>
            </form>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
