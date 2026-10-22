<?php
declare(strict_types=1);
$content = require __DIR__ . '/../data/logistica.php';
$requested = $_GET['lang'] ?? 'es';
$lang = is_string($requested) && isset($content[$requested]) ? $requested : 'es';
$c = $content[$lang];
$e = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$lines = static fn(string $value): string => nl2br(htmlspecialchars(str_replace('\\n', "\n", $value), ENT_QUOTES, 'UTF-8'));
$asset = static fn(string $path): string => '/' . $path . '?v=' . filemtime(__DIR__ . '/../' . $path);
$image = static fn(string $name): string => '/' . app_image_path('assets/images/logistica/' . $name . '.webp');
$portfolio = '/?lang=' . $lang;
require __DIR__ . '/../app/seo.php';
$seo = app_seo_page('logistics', $lang);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080e17">
    <?php require __DIR__ . '/../components/seo-head.php'; ?>
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $e($asset('assets/css/logistica.css')) ?>">
    <script src="<?= $e($asset('assets/js/logistica.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main"><?= $e($c['skip']) ?></a>
<header class="site-header">
    <div class="shell header-inner">
        <a class="wordmark" href="<?= $portfolio ?>#projects" aria-label="<?= $e($c['back']) ?>">jacoto<span>✳</span><span class="brand-divider"></span><span class="project-brand">MEIBIT<span class="mono">CASE STUDY / 01</span></span></a>
        <button class="menu-button" aria-expanded="false" aria-controls="project-nav" hidden><?= $e($c['menu']) ?> <span aria-hidden="true">☰</span></button>
        <nav id="project-nav" aria-label="<?= $e($c['menu']) ?>"><?php foreach ($c['nav'] as $id => $label): ?><a href="#<?= $id ?>"><?= $e($label) ?></a><?php endforeach; ?></nav>
        <div class="header-tools"><div class="languages"><a href="?lang=es" lang="es" hreflang="es" <?= $lang === 'es' ? 'aria-current="true"' : '' ?>>ES</a><span>/</span><a href="?lang=en" lang="en" hreflang="en" <?= $lang === 'en' ? 'aria-current="true"' : '' ?>>EN</a></div><button class="theme-button" aria-label="<?= $e($c['theme']) ?>" aria-pressed="false" hidden>◐</button></div>
    </div>
    <div class="reading-progress" aria-hidden="true"></div>
</header>
<main id="main">
    <section class="hero" id="top" aria-labelledby="hero-title">
        <div class="hero-grid" aria-hidden="true"></div>
        <div class="shell">
            <div class="hero-topline mono"><span><i class="status-dot"></i><?= $e($c['eyebrow']) ?></span><span>MEIBIT / .NET + ANGULAR</span></div>
            <div class="hero-layout">
                <div class="hero-copy"><h1 id="hero-title"><?= $e($c['headline'][0]) ?><br><span><?= $e($c['headline'][1]) ?></span></h1><p class="hero-intro"><?= $e($c['intro']) ?></p><div class="actions"><a class="button primary" href="#overview"><?= $e($c['explore']) ?> <span aria-hidden="true">↗</span></a><a class="text-link" href="#platform"><?= $e($c['view']) ?> <span aria-hidden="true">↓</span></a></div><p class="project-credit"><span><?= $e($c['project_for']) ?></span><strong>MEIBIT</strong></p></div>
                <div class="hero-visual">
                    <div class="visual-coordinate mono" aria-hidden="true">SYS.01 / DOCK OPERATIONS</div>
                    <figure class="terminal-frame"><div class="frame-bar mono"><span><i></i><i></i><i></i></span><span><?= $e($c['device']) ?></span><span aria-hidden="true">↗</span></div><a class="media-link terminal-image" href="/images/proyecto_logistica/imagen1.jpg" data-lightbox data-title="<?= $e($c['device']) ?>" aria-label="<?= $e($c['zoom'] . ': ' . $c['device']) ?>"><img src="<?= $image('imagen1') ?>" width="1078" height="1006" alt="<?= $e($c['device_alt']) ?>" fetchpriority="high"><span class="image-expand" aria-hidden="true">↗</span></a></figure>
                    <div class="signal-bridge" aria-hidden="true"><span></span><span></span><span></span></div>
                    <div class="floating-session"><div class="mono">SESSION TRACE <span>05 / 10</span></div><img src="<?= $image('imagen4') ?>" width="954" height="1632" alt="<?= $e($c['session_alt']) ?>"><span class="floating-caption mono"><?= $e($c['capture']) ?></span></div>
                    <p class="visual-note mono"><span aria-hidden="true">└─</span> <?= $e($c['hero_note']) ?></p>
                </div>
            </div>
            <dl class="tech-rail"><?php foreach ($c['meta'] as [$label, $value]): ?><div><dt class="mono"><?= $e($label) ?></dt><dd><?= $e($value) ?></dd></div><?php endforeach; ?></dl>
        </div>
    </section>

    <section class="section" id="overview" aria-labelledby="overview-title"><div class="shell">
        <div class="section-heading" data-reveal><p class="eyebrow"><?= $e($c['overview_kicker']) ?></p><h2 id="overview-title"><?= $e($c['overview_title']) ?></h2></div>
        <div class="overview-grid" data-reveal><article class="context-block"><span class="mono">/ A</span><h3><?= $e($c['challenge_label']) ?></h3><p><?= $e($c['challenge']) ?></p></article><article class="context-block"><span class="mono">/ B</span><h3><?= $e($c['solution_label']) ?></h3><p><?= $e($c['solution']) ?></p></article><aside class="contribution"><p class="mono"><?= $e($c['contribution']) ?></p><ul><?php foreach ($c['contributions'] as $item): ?><li><?= $e($item) ?></li><?php endforeach; ?></ul></aside></div>
    </div></section>

    <section class="section architecture" id="architecture" aria-labelledby="architecture-title"><div class="shell">
        <div class="section-heading split-heading" data-reveal><div><p class="eyebrow"><?= $e($c['architecture_kicker']) ?></p><h2 id="architecture-title"><?= $lines($c['architecture_title']) ?></h2></div><p><?= $e($c['architecture_intro']) ?></p></div>
        <div class="architecture-flow" data-reveal><?php foreach ($c['architecture'] as $index => [$number, $title, $tech, $description]): ?><article class="architecture-node"><div class="node-top mono"><span><?= $number ?> / <?= $e($c['flow_labels'][$index]) ?></span><span class="node-symbol" aria-hidden="true"><?= ['⌘', '⇄', '▥'][$index] ?></span></div><h3><?= $e($title) ?></h3><p class="node-tech mono"><?= $e($tech) ?></p><p><?= $e($description) ?></p></article><?php endforeach; ?></div>
    </div></section>

    <section class="section" id="timeline" aria-labelledby="timeline-title"><div class="shell">
        <div class="section-heading split-heading" data-reveal><div><p class="eyebrow"><?= $e($c['timeline_kicker']) ?></p><h2 id="timeline-title"><?= $lines($c['timeline_title']) ?></h2></div><p><?= $e($c['timeline_intro']) ?></p></div>
        <div class="process-layout" data-reveal>
            <div class="process-console"><div class="console-top mono"><span>PROCESS SEQUENCE</span><span>10 / EVENTS</span></div><ol class="stage-list"><?php foreach ($c['stages'] as $index => [$title, $text]): ?><li><button type="button" data-stage="<?= $index ?>" aria-pressed="<?= $index === 4 ? 'true' : 'false' ?>" aria-controls="stage-detail"><span class="stage-number mono"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><span><?= $e($title) ?></span><span class="stage-marker" aria-hidden="true"></span></button><p class="stage-source"><?= $e($text) ?></p></li><?php endforeach; ?></ol></div>
            <div class="process-detail"><div class="stage-detail" id="stage-detail" aria-live="polite" aria-atomic="true"><p class="eyebrow"><?= $e($c['stage_label']) ?> <span id="stage-count">05 / 10</span></p><h3 id="stage-title"><?= $e($c['stages'][4][0]) ?></h3><p id="stage-description"><?= $e($c['stages'][4][1]) ?></p><div class="stage-meter" aria-hidden="true"><?php for ($i = 0; $i < 10; $i++): ?><span class="<?= $i <= 4 ? 'filled' : '' ?>"></span><?php endfor; ?></div></div><div class="stage-controls" hidden><button type="button" data-step="-1" aria-label="<?= $e($c['previous']) ?>">←</button><span class="mono">EXPLORE THE FLOW</span><button type="button" data-step="1" aria-label="<?= $e($c['next']) ?>">→</button></div><figure class="process-image"><a class="media-link" href="/images/proyecto_logistica/imagen4.jpg" data-lightbox data-title="<?= $e($c['gallery'][2][1]) ?>" aria-label="<?= $e($c['zoom'] . ': ' . $c['gallery'][2][0]) ?>"><img src="<?= $image('imagen4') ?>" width="954" height="1632" loading="lazy" alt="<?= $e($c['session_alt']) ?>"><span class="image-expand" aria-hidden="true">↗</span></a><figcaption class="mono"><?= $e($c['capture']) ?></figcaption></figure><p class="stage-note"><?= $e($c['stage_note']) ?></p></div>
        </div>
    </div></section>

    <section class="section platform" id="platform" aria-labelledby="platform-title"><div class="shell">
        <div class="section-heading split-heading" data-reveal><div><p class="eyebrow"><?= $e($c['platform_kicker']) ?></p><h2 id="platform-title"><?= $lines($c['platform_title']) ?></h2></div><p><?= $e($c['platform_intro']) ?></p></div>
        <div class="gallery" data-reveal><div class="gallery-tabs" aria-label="<?= $e($c['nav']['platform']) ?>"><?php foreach ($c['gallery'] as $index => $item): ?><a href="#view-<?= $index ?>" id="tab-<?= $index ?>" data-gallery-tab="<?= $index ?>"><span class="mono">0<?= $index + 1 ?></span><?= $e($item[0]) ?></a><?php endforeach; ?></div>
        <?php foreach ($c['gallery'] as $index => [$label, $title, $description, $file, $alt]): ?><article class="gallery-panel" id="view-<?= $index ?>"><div class="gallery-copy"><span class="mono panel-index">VIEW / 0<?= $index + 1 ?></span><h3><?= $e($title) ?></h3><p><?= $e($description) ?></p><a class="text-link" href="/images/proyecto_logistica/<?= $file ?>.jpg" data-lightbox data-title="<?= $e($title) ?>"><?= $e($c['zoom']) ?> ↗</a><span class="capture-label mono"><?= $e($c['capture']) ?></span></div><a class="gallery-image media-link" href="/images/proyecto_logistica/<?= $file ?>.jpg" data-lightbox data-title="<?= $e($title) ?>" aria-label="<?= $e($c['zoom'] . ': ' . $label) ?>"><img src="<?= $image($file) ?>" loading="lazy" alt="<?= $e($alt) ?>"><span class="image-expand" aria-hidden="true">↗</span></a></article><?php endforeach; ?></div>
        <h3 class="modules-heading"><?= $e($c['modules_title']) ?></h3><div class="module-grid"><?php foreach ($c['modules'] as [$number, $title, $description]): ?><article data-reveal><span class="mono"><?= $number ?> /</span><h4><?= $e($title) ?></h4><p><?= $e($description) ?></p></article><?php endforeach; ?></div>
    </div></section>

    <section class="section" id="kpis" aria-labelledby="kpi-title"><div class="shell">
        <div class="section-heading split-heading" data-reveal><div><p class="eyebrow"><?= $e($c['kpi_kicker']) ?></p><h2 id="kpi-title"><?= $lines($c['kpi_title']) ?></h2></div><p><?= $e($c['kpi_intro']) ?></p></div>
        <div class="report-grid"><?php foreach ($c['reports'] as [$title, $description, $file]): ?><article class="report-card" data-reveal><div class="report-heading"><div><span class="mono">SESSION REPORT</span><h3><?= $e($title) ?></h3></div><span aria-hidden="true">↗</span></div><a class="report-image media-link" href="/images/proyecto_logistica/<?= $file ?>.jpg" data-lightbox data-title="<?= $e($title . ' · ' . $description) ?>" aria-label="<?= $e($c['zoom'] . ': ' . $title) ?>"><img src="<?= $image($file) ?>" width="974" height="<?= $file === 'infografico1' ? '1388' : '1436' ?>" loading="lazy" alt="<?= $e($c['report_alt']) ?>"><span class="image-expand" aria-hidden="true">↗</span></a><div class="report-footer"><p><?= $e($description) ?></p><span class="mono"><?= $e($c['capture']) ?></span></div></article><?php endforeach; ?></div>
        <div class="analytics-grid"><?php foreach ($c['analytics'] as [$title, $description]): ?><article><h3><?= $e($title) ?></h3><p><?= $e($description) ?></p></article><?php endforeach; ?></div><p class="export-note mono"><span aria-hidden="true">↓</span><?= $e($c['exports']) ?></p>
    </div></section>

    <section class="section value" id="value" aria-labelledby="value-title"><div class="shell"><div class="section-heading" data-reveal><p class="eyebrow"><?= $e($c['value_kicker']) ?></p><h2 id="value-title"><?= $lines($c['value_title']) ?></h2></div><div class="value-grid"><?php foreach ($c['values'] as $index => [$title, $description]): ?><article data-reveal><span class="value-number">0<?= $index + 1 ?></span><h3><?= $e($title) ?></h3><p><?= $e($description) ?></p></article><?php endforeach; ?></div></div></section>
    <section class="section contact" id="contact" aria-labelledby="contact-title"><div class="shell" data-reveal><p class="eyebrow"><?= $e($c['contact_kicker']) ?></p><h2 id="contact-title"><?= $lines($c['contact_title']) ?></h2><p><?= $e($c['contact_intro']) ?></p><div class="actions"><a class="button primary" href="<?= $portfolio ?>#contact"><?= $e($c['contact_cta']) ?> ↗</a><a class="text-link" href="<?= $portfolio ?>#projects">← <?= $e($c['back']) ?></a></div></div></section>
</main>
<footer><div class="shell"><a class="wordmark" href="<?= $portfolio ?>">jacoto<span>✳</span></a><span class="mono"><?= $e($c['footer']) ?></span><a href="#top" aria-label="<?= $e($c['nav']['overview']) ?>">↑</a></div></footer>
<dialog class="lightbox" aria-labelledby="lightbox-title"><div class="lightbox-toolbar"><h2 id="lightbox-title"></h2><button type="button" class="lightbox-close" aria-label="<?= $e($c['close']) ?>">✕</button></div><div class="lightbox-image-wrap"><img alt="" id="lightbox-image"></div><a id="lightbox-original" class="text-link" target="_blank" rel="noopener noreferrer"><?= $e($c['original']) ?> ↗</a></dialog>
</body>
</html>
