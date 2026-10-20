<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/editor.php';
require __DIR__ . '/../app/editor_view.php';
try {
    $pdo = app_db();
    $currentUser = admin_current_user($pdo);
} catch (Throwable $exception) {
    $currentUser = null;
}
if (!$currentUser) { header('Location: /admin/'); exit; }
app_run_portfolio_migrations($pdo);
app_seed_portfolio_content($pdo);
studio_migrate($pdo);
$sections = studio_sections();
$section = (string) ($_GET['section'] ?? 'overview');
if (!isset($sections[$section])) $section = 'overview';
$csrf = app_csrf_token();
$profile = app_portfolio_profile_from_db($pdo);
$currentRole = $profile['experience'][0] ?? null;
$asset = static fn($file) => '/' . $file . '?v=' . filemtime(APP_BASE_PATH . '/' . $file);
$sectionEntities = ['skills' => 'skill', 'highlights' => 'highlight', 'studies' => 'study', 'services' => 'service', 'projects' => 'project', 'experience' => 'experience', 'hero' => 'hero_card'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="robots" content="noindex,nofollow">
    <title><?= e($sections[$section]['label']) ?> · Jacoto Studio</title>
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e($asset('assets/css/studio.css')) ?>">
    <script src="<?= e($asset('assets/js/studio.js')) ?>" defer></script>
</head>
<body class="studio" data-user="<?= (int) $currentUser['id'] ?>" data-section="<?= e($section) ?>">
<a class="studio-skip" href="#studioMain">Saltar al contenido</a>
<button class="sidebar-backdrop" type="button" aria-label="Cerrar navegación" hidden></button>
<aside class="studio-sidebar" id="studioSidebar">
    <a class="studio-brand" href="/admin/content.php">jacoto<span>STUDIO / 01</span></a>
    <div class="workspace-label"><span class="workspace-dot"></span>Tu espacio de edición</div>
    <nav aria-label="Secciones del administrador">
        <?php foreach ($sections as $key => $item): ?>
            <?php if ($key === 'profile'): ?><p class="nav-group-label">Portfolio</p><?php elseif ($key === 'hero'): ?><p class="nav-group-label">Identidad y contenido</p><?php endif; ?>
            <a href="/admin/content.php?section=<?= e($key) ?>" <?= $section === $key ? 'aria-current="page"' : '' ?>><?= studio_icon($item['icon']) ?><span><?= e($item['label']) ?></span><?php if ($section === $key): ?><i></i><?php endif; ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom"><a href="/" target="_blank" rel="noopener">Ver portfolio <?= studio_icon('arrow') ?></a><div class="sidebar-user"><span class="user-avatar"><?= e(mb_strtoupper(mb_substr($currentUser['username'], 0, 1))) ?></span><div><strong><?= e($currentUser['username']) ?></strong><small>Administrador</small></div><form method="post" action="/admin/logout.php" data-logout><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><button type="submit" class="icon-button" aria-label="Guardar y cerrar sesión" title="Cerrar sesión"><?= studio_icon('logout') ?></button></form></div></div>
</aside>
<div class="studio-workspace">
    <header class="studio-topbar"><div class="topbar-path"><button type="button" class="icon-button mobile-nav-button" id="studioMenu" aria-label="Abrir navegación" aria-controls="studioSidebar" aria-expanded="false"><?= studio_icon('grid') ?></button><span>Workspace</span><span class="path-divider">/</span><strong><?= e($sections[$section]['label']) ?></strong></div><div class="topbar-actions"><span class="global-save-state" id="globalSaveState" role="status"><i></i><span>Todo guardado</span></span><button class="studio-button primary" id="saveAll" type="button" disabled>Guardar cambios <span>↗</span></button></div></header>
    <main class="studio-main" id="studioMain" tabindex="-1">
        <div class="studio-page-heading"><div><p class="studio-eyebrow">JACOTO / CONTENT STUDIO</p><h1><?= e($section === 'overview' ? 'Todo en su sitio.' : $sections[$section]['label']) ?></h1><p><?= e($sections[$section]['description']) ?></p></div><a class="studio-button" href="<?= $section === 'experience' ? '/experiencia' : ($section === 'projects' ? '/proyectos' : '/') ?>" target="_blank" rel="noopener">Vista previa <?= studio_icon('arrow') ?></a></div>
        <div id="studioNotice" class="studio-notice" role="status" hidden></div>
        <noscript><div class="studio-notice">El guardado automático necesita JavaScript. Puedes utilizar el botón Guardar de cada bloque.</div></noscript>
        <?php if (isset($_GET['saved'])): ?><div class="studio-notice">Cambios guardados correctamente.</div><?php endif; ?>
        <?php if ($section === 'overview'): ?>
            <section class="studio-welcome"><div><span class="studio-eyebrow">TU PORTFOLIO, EN VIVO</span><h2>Tu siguiente<br>gran versión.</h2><p>Actualiza tu historia, elige qué proyectos mostrar y cuida cada detalle. Los cambios se guardan en el momento.</p><a class="studio-button primary" href="/admin/content.php?section=experience">Actualizar mi experiencia ↗</a></div><div class="welcome-portrait"><img src="<?= e($profile['cv_photo'] === 'images/perfil_joseangel_linkedin.jpg' ? '/assets/images/portrait-480.webp' : '/' . ltrim($profile['cv_photo'], '/')) ?>" alt="<?= e($profile['name']) ?>" width="480" height="540"><span>j.</span></div></section>
            <div class="studio-stats"><?php foreach ([['projects', count($profile['projects']), 'Proyectos publicados'], ['experience', count($profile['experience']), 'Etapas profesionales'], ['skills', count($profile['skills']), 'Tecnologías'], ['texts', 'ES / EN', 'Idiomas del portfolio']] as [$key, $number, $label]): ?><a href="/admin/content.php?section=<?= e($key) ?>"><span><?= e($label) ?></span><strong><?= e((string) $number) ?></strong><?= studio_icon('arrow') ?></a><?php endforeach; ?></div>
            <div class="dashboard-grid"><section class="dashboard-card"><div class="dashboard-card-heading"><h2>Ahora mismo</h2><span class="visibility-badge">Publicado</span></div><?php if ($currentRole): ?><span class="studio-eyebrow">EXPERIENCIA PRINCIPAL</span><h3><?= e($currentRole['company']) ?></h3><p><?= e($currentRole['title']['es']) ?></p><div class="dashboard-tags"><?php foreach (array_slice($currentRole['tags'], 0, 6) as $tag): ?><span><?= e($tag) ?></span><?php endforeach; ?></div><?php endif; ?><a class="text-button" href="/admin/content.php?section=experience">Editar trayectoria ↗</a></section><section class="dashboard-card"><h2>Edición sin fricción</h2><ol class="studio-how"><li><span>01</span><div><strong>Abre un bloque</strong><p>Solo ves los campos que necesitas.</p></div></li><li><span>02</span><div><strong>Edita a tu ritmo</strong><p>El estado indica cuándo se ha guardado.</p></div></li><li><span>03</span><div><strong>Cierra y sigue</strong><p>Guardamos antes de cerrar o cambiar de sección.</p></div></li></ol></section></div>
            <section class="quick-links"><h2>A un clic</h2><div><a href="/admin/content.php?section=profile"><?= studio_icon('user') ?> Mi perfil <span>↗</span></a><a href="/admin/content.php?section=projects"><?= studio_icon('layers') ?> Mis proyectos <span>↗</span></a><a href="/admin/content.php?section=design"><?= studio_icon('type') ?> Titulares del diseño <span>↗</span></a><a href="/cv.php?lang=es&print=1" target="_blank" rel="noopener"><?= studio_icon('book') ?> Generar mi CV <span>↗</span></a></div></section>
        <?php else: ?>
            <div class="studio-filterbar"><label class="studio-search"><?= studio_icon('search') ?><span class="sr-only">Buscar en esta sección</span><input type="search" id="studioSearch" placeholder="Buscar en <?= e(mb_strtolower($sections[$section]['label'])) ?>…" autocomplete="off"><kbd>Ctrl K</kbd></label><?php if (isset($sectionEntities[$section])): ?><div class="status-filters" role="group" aria-label="Filtrar por visibilidad"><button type="button" data-filter="all" aria-pressed="true">Todos</button><button type="button" data-filter="1" aria-pressed="false">Publicados</button><button type="button" data-filter="0" aria-pressed="false">Ocultos</button></div><a class="studio-button" href="#newRecord" data-new-record>+ Añadir</a><?php endif; ?></div>
            <div class="editor-list" id="editorList">
            <?php if ($section === 'profile'): ?>
                <?php foreach (['profile', 'intro', 'hobby'] as $entity) studio_render_card($pdo, $entity, studio_record($pdo, $entity, 'main'), $csrf, false, $entity === 'profile'); ?>
            <?php elseif ($section === 'texts' || $section === 'design'): ?>
                <?php foreach (studio_records($pdo, 'text') as $record): if (str_starts_with($record['id'], 'editorial_') !== ($section === 'design') || $record['id'] === 'hero_typewriter') continue; studio_render_card($pdo, 'text', $record, $csrf); endforeach; ?>
            <?php else: ?>
                <?php if ($section === 'hero') studio_render_card($pdo, 'text', studio_record($pdo, 'text', 'hero_typewriter'), $csrf); ?>
                <?php $entity = $sectionEntities[$section]; foreach (studio_records($pdo, $entity) as $record) studio_render_card($pdo, $entity, $record, $csrf, false, ($_GET['open'] ?? '') === $record['id']); ?>
                <div id="newRecord"><?php studio_render_card($pdo, $entity, ['id' => app_uid()], $csrf, true); ?></div>
            <?php endif; ?>
            </div>
            <div class="studio-empty" id="studioEmpty" hidden><h2>No hay coincidencias.</h2><p>Prueba otra búsqueda o cambia el filtro.</p></div>
            <p class="studio-bottom-note"><?= studio_icon('check') ?> Cada bloque se guarda de forma independiente. <span>Ctrl / ⌘ + S para guardar todo.</span></p>
        <?php endif; ?>
        <footer class="studio-footer"><span>JACOTO STUDIO</span><span>Menos fricción. Más intención.</span></footer>
    </main>
</div>
<dialog id="studioDeleteDialog" class="studio-dialog"><form method="dialog"><span class="studio-eyebrow">ELIMINAR CONTENIDO</span><h2>¿Eliminar este bloque?</h2><p>También se eliminarán sus enlaces o proyectos asociados. Para retirarlo temporalmente, puedes ocultarlo.</p><div><button class="studio-button" value="cancel">Conservar</button><button class="studio-button danger-fill" value="delete">Eliminar bloque</button></div></form></dialog>
</body>
</html>
