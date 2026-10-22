<?php
declare(strict_types=1);
$translations = require __DIR__ . '/../data/paso-seguro.php';
$requestedLang = $_GET['lang'] ?? 'es';
$lang = is_string($requestedLang) && isset($translations[$requestedLang]) ? $requestedLang : 'es';
$copy = $translations[$lang];
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$asset = static fn(string $path): string => '/' . $path . '?v=' . filemtime(__DIR__ . '/../' . $path);
$portfolio = '/?lang=' . $lang;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($copy['title']) ?></title>
    <meta name="description" content="<?= $escape($copy['description']) ?>">
    <meta name="theme-color" content="#dc2626">
    <meta property="og:title" content="<?= $escape($copy['title']) ?>">
    <meta property="og:description" content="<?= $escape($copy['description']) ?>">
    <meta property="og:image" content="https://lawebdejacoto.com/assets/images/paso-seguro/secuencia_4_pasoseguro.webp">
    <meta property="og:type" content="website">
    <link rel="icon" href="/assets/images/paso-seguro/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= $escape($asset('assets/css/paso-seguro.css')) ?>">
    <script src="<?= $escape($asset('assets/js/paso-seguro.js')) ?>" defer></script>
</head>
<body>
    <a class="skip-link" href="#main"><?= $escape($copy['skip']) ?></a>
    <div class="portfolio-bar"><div class="wide"><a href="<?= $portfolio ?>#projects">← <?= $escape($copy['back']) ?></a><span>JACOTO <span aria-hidden="true">/</span> <?= $escape($copy['showcase']) ?></span></div></div>
    <header class="site-header">
        <div class="nav-row wide">
            <a class="brand" href="#top"><span class="brand-mark" aria-hidden="true">●</span>Paso Seguro</a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav" hidden><span aria-hidden="true">☰</span> <?= $escape($copy['menu']) ?></button>
            <nav id="main-nav" aria-label="<?= $escape($copy['menu']) ?>">
                <?php foreach ($copy['nav'] as $id => $label): ?>
                    <a href="#<?= $id ?>"><?= $escape($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="nav-tools">
                <div class="languages" aria-label="Language"><a href="?lang=es" lang="es" hreflang="es" <?= $lang === 'es' ? 'aria-current="true"' : '' ?>>ES</a><span aria-hidden="true">/</span><a href="?lang=en" lang="en" hreflang="en" <?= $lang === 'en' ? 'aria-current="true"' : '' ?>>EN</a></div>
                <button class="theme-toggle" type="button" aria-label="<?= $escape($copy['theme']) ?>" aria-pressed="false" hidden><span aria-hidden="true">◐</span></button>
            </div>
        </div>
        <div class="scroll-track" aria-hidden="true"><div></div></div>
    </header>
    <main id="main">
        <section class="hero" id="top" aria-labelledby="hero-title">
            <div class="container hero-inner">
                <span class="badge"><span aria-hidden="true">◇</span> <?= $escape($copy['tagline']) ?></span>
                <h1 id="hero-title">Paso Seguro</h1>
                <p class="hero-headline"><?= $escape($copy['headline']) ?></p>
                <p class="hero-description"><?= $escape($copy['intro']) ?></p>
                <div class="actions"><a class="button primary" href="#contact"><?= $escape($copy['primary']) ?> <span aria-hidden="true">→</span></a><a class="button" href="#solution"><?= $escape($copy['secondary']) ?></a></div>
                <figure class="simulation">
                    <div class="simulation-shell">
                        <div class="frames" role="img" aria-label="<?= $escape($copy['scene_alt']) ?>">
                            <?php for ($frame = 1; $frame <= 6; $frame++): ?>
                                <img src="/assets/images/paso-seguro/secuencia_<?= $frame ?>_pasoseguro.webp" width="1000" height="562" alt="" aria-hidden="true" class="frame<?= $frame === 1 ? ' is-active' : '' ?>" <?= $frame === 1 ? 'fetchpriority="high"' : 'decoding="async"' ?>>
                            <?php endfor; ?>
                            <span class="preview-badge"><i></i><?= $escape($copy['preview']) ?></span>
                        </div>
                    </div>
                    <div class="simulation-controls" hidden>
                        <button class="play-toggle" type="button" aria-pressed="false" data-play="<?= $escape($copy['play']) ?>" data-pause="<?= $escape($copy['pause']) ?>"><?= $escape($copy['pause']) ?></button>
                        <div class="frame-buttons" role="group" aria-label="<?= $escape($copy['preview']) ?>">
                            <?php for ($frame = 1; $frame <= 6; $frame++): ?><button type="button" data-frame="<?= $frame - 1 ?>" aria-label="<?= $escape($copy['frame']) ?> <?= $frame ?>" aria-pressed="<?= $frame === 1 ? 'true' : 'false' ?>"></button><?php endfor; ?>
                        </div>
                        <span class="frame-counter" aria-hidden="true">01 / 06</span>
                    </div>
                    <figcaption><?= $escape($copy['caption']) ?></figcaption>
                </figure>
            </div>
        </section>
        <section class="section" id="problem" aria-labelledby="problem-title">
            <div class="container split" data-reveal>
                <div><p class="eyebrow">01 / <?= $escape($copy['nav']['problem']) ?></p><h2 id="problem-title"><?= $escape($copy['problem']['title']) ?></h2><p class="section-description"><?= $escape($copy['problem']['description']) ?></p><p class="problem-note"><?= $escape($copy['problem']['note']) ?></p></div>
                <figure class="picture"><img src="/assets/images/paso-seguro/stop_con_laser_caso_real.webp" width="1536" height="1024" loading="lazy" decoding="async" alt="<?= $escape($copy['scene_alt']) ?>"><figcaption><?= $escape($copy['problem']['caption']) ?></figcaption></figure>
            </div>
        </section>
        <section class="section muted" id="solution" aria-labelledby="solution-title">
            <div class="container" data-reveal>
                <div class="section-heading"><p class="eyebrow">02 / <?= $escape($copy['solution']['eyebrow']) ?></p><h2 id="solution-title"><?= $escape($copy['solution']['title']) ?></h2></div>
                <ol class="cards steps">
                    <?php foreach ($copy['solution']['items'] as $index => [$title, $description]): ?>
                        <li class="card"><span class="step-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= $escape($title) ?></h3><p><?= $escape($description) ?></p></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
        <section class="section" id="installation" aria-labelledby="installation-title">
            <div class="container" data-reveal>
                <div class="split installation-intro">
                    <div><p class="eyebrow">03 / <?= $escape($copy['nav']['installation']) ?></p><h2 id="installation-title"><?= $escape($copy['installation']['title']) ?></h2><p class="section-description"><?= $escape($copy['installation']['description']) ?></p></div>
                    <img class="device-image" src="/assets/images/paso-seguro/stop_dispositivo.webp" width="1608" height="978" loading="lazy" decoding="async" alt="<?= $escape($copy['installation']['image_alt']) ?>">
                </div>
                <div class="cards">
                    <?php foreach ($copy['installation']['items'] as [$title, $description]): ?><article class="card"><span class="card-icon" aria-hidden="true">✓</span><h3><?= $escape($title) ?></h3><p><?= $escape($description) ?></p></article><?php endforeach; ?>
                </div>
                <details class="installation-details"><summary><?= $escape($copy['installation']['details']) ?></summary><ul><?php foreach ($copy['installation']['parts'] as $part): ?><li><?= $escape($part) ?></li><?php endforeach; ?></ul></details>
            </div>
        </section>
        <section class="section muted" id="benefits" aria-labelledby="benefits-title">
            <div class="container" data-reveal>
                <div class="section-heading"><p class="eyebrow">04 / <?= $escape($copy['nav']['benefits']) ?></p><h2 id="benefits-title"><?= $escape($copy['benefits']['title']) ?></h2></div>
                <div class="cards benefits">
                    <?php foreach ($copy['benefits']['items'] as [$title, $description]): ?><article class="card"><span class="card-icon" aria-hidden="true">✓</span><h3><?= $escape($title) ?></h3><p><?= $escape($description) ?></p></article><?php endforeach; ?>
                </div>
            </div>
        </section>
        <section class="section" id="use-cases" aria-labelledby="use-cases-title">
            <div class="container" data-reveal>
                <div class="section-heading"><p class="eyebrow">05 / <?= $escape($copy['nav']['use-cases']) ?></p><h2 id="use-cases-title"><?= $escape($copy['use-cases']['title']) ?></h2></div>
                <div class="cards use-cases">
                    <?php foreach ($copy['use-cases']['items'] as $index => [$title, $description]): ?><article class="card"><span class="case-number" aria-hidden="true">0<?= $index + 1 ?></span><h3><?= $escape($title) ?></h3><p><?= $escape($description) ?></p></article><?php endforeach; ?>
                </div>
            </div>
        </section>
        <section class="section contact" id="contact" aria-labelledby="contact-title">
            <div class="container" data-reveal><p class="eyebrow">06 / <?= $escape($copy['showcase']) ?></p><h2 id="contact-title"><?= $escape($copy['contact_title']) ?></h2><p class="section-description"><?= $escape($copy['contact_description']) ?></p><div class="stack"><span>Laravel</span><span>Vue 3</span><span>TypeScript</span><span>IoT</span><span>SaaS</span></div><a class="button primary" href="<?= $portfolio ?>#contact"><?= $escape($copy['contact_button']) ?> <span aria-hidden="true">↗</span></a><p class="contact-note"><?= $escape($copy['contact_note']) ?></p></div>
        </section>
    </main>
    <footer><div class="container footer-inner"><div><a class="brand" href="#top"><span class="brand-mark" aria-hidden="true">●</span>Paso Seguro</a><p><?= $escape($copy['footer']) ?></p></div><div class="footer-links"><a href="<?= $portfolio ?>#projects">← <?= $escape($copy['back']) ?></a><a href="#top"><?= $escape($copy['top']) ?> ↑</a></div></div></footer>
</body>
</html>
