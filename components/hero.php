<?php
/** @var array $profile */ /** @var array $t */ /** @var array $editorial */
$hw = $heroCards ?: [
    ['label' => 'Full Stack', 'items' => [['label' => 'Laravel / PHP', 'url' => '#experience'], ['label' => '.NET / C#', 'url' => '#experience'], ['label' => 'Angular', 'url' => '#experience'], ['label' => 'SQL / APIs', 'url' => '#services']]],
    ['label' => 'ERP', 'items' => [['label' => 'Odoo', 'url' => '#experience'], ['label' => 'SAP', 'url' => '#experience'], ['label' => $lang === 'es' ? 'Automatizaciones' : 'Automation', 'url' => '#services'], ['label' => $lang === 'es' ? 'Procesos internos' : 'Internal processes', 'url' => '#services']]],
    ['label' => 'E-commerce', 'items' => [['label' => 'Shopify', 'url' => '#experience'], ['label' => 'SticNow', 'url' => 'https://sticnow.com'], ['label' => 'Checkout', 'url' => '#services'], ['label' => $lang === 'es' ? 'Conversión' : 'Conversion', 'url' => '#projects']]],
    ['label' => $lang === 'es' ? 'Logística' : 'Logistics', 'items' => [['label' => $lang === 'es' ? 'Proyecto Logístico' : 'Logistics Project', 'url' => '/proyecto-logistica/?lang=' . $lang], ['label' => $lang === 'es' ? 'Trazabilidad' : 'Traceability', 'url' => '#projects'], ['label' => 'Dashboards', 'url' => '#services']]],
    ['label' => $lang === 'es' ? 'Producto' : 'Product', 'items' => [['label' => 'Jacoto Fotografía', 'url' => 'https://jacotofotografia.com'], ['label' => 'Landings', 'url' => '#services'], ['label' => $lang === 'es' ? 'SEO técnico' : 'Technical SEO', 'url' => '#projects'], ['label' => $lang === 'es' ? 'Analítica' : 'Analytics', 'url' => '#contact']]],
];
$portrait = $imageFor($profile['cv_photo'] ?? 'images/perfil_joseangel_linkedin.jpg');
$currentPosition = null;
foreach ($profile['experience'] as $position) {
    if (!empty($position['start_date']) && $position['start_date'] <= date('Y-m-d') && empty($position['end_date'])) {
        $currentPosition = $position;
        break;
    }
}
?>
<section class="hero container" id="top" aria-labelledby="heroTitle">
    <div class="hero-topline micro"><span><i class="status-dot" aria-hidden="true"></i><?= htmlspecialchars($t['hero_eyebrow']) ?></span><span><?= htmlspecialchars($profile['location'][$lang] ?? $profile['location']['es']) ?> <span aria-hidden="true">↗</span></span></div>
    <div class="hero-scroll-scene">
    <div class="hero-stage">
        <div class="hero-scenography" aria-hidden="true">
            <span class="hero-scene-coordinate">FIG. 01 — J/A</span>
            <svg class="hero-orbit" viewBox="0 0 600 600" fill="none">
                <g stroke="currentColor" stroke-width=".75">
                    <?php for ($orbit = 0; $orbit < 18; $orbit++): ?>
                    <ellipse cx="300" cy="300" rx="<?= 65 + $orbit * 10 ?>" ry="240" style="--orbit-angle:<?= $orbit * 10 ?>deg;--orbit-scale:<?= number_format(240 / (65 + $orbit * 10), 4, '.', '') ?>"/>
                    <?php endfor; ?>
                </g>
                <circle cx="300" cy="60" r="5" fill="currentColor"/>
            </svg>
            <span class="hero-scene-axis"></span>
        </div>
        <div class="hero-title-wrap">
            <p class="hero-direction micro"><span class="hero-direction-line" aria-hidden="true"></span><?= htmlspecialchars($editorial['hero_direction']) ?></p>
            <div class="hero-type-space">
            <div class="hero-title-motion">
            <h1 class="hero-title" id="heroTitle">
                <span class="hero-opening"><span><?= htmlspecialchars($editorial['headline'][0]) ?></span></span>
                <span class="hero-middle"><span class="hero-connector"><?= htmlspecialchars($editorial['headline'][1]) ?></span><svg class="hero-simplicity" viewBox="0 0 100 100" fill="none" aria-hidden="true" focusable="false">
                    <g class="simplicity-lines" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path class="simplicity-line" d="M30 38h40"/>
                        <path class="simplicity-line" d="M30 50h40"/>
                        <path class="simplicity-line" d="M30 62h40"/>
                    </g>
                    <path class="simplicity-check" d="m30 50 14 14 28-28" pathLength="1" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg></span>
                <span class="hero-ending"><span><?php $heroEnding = $editorial['headline'][2]; ?><?= htmlspecialchars(str_ends_with($heroEnding, '.') ? substr($heroEnding, 0, -1) : $heroEnding) ?><?php if (str_ends_with($heroEnding, '.')): ?><span class="hero-ending-dot">.</span><?php endif; ?></span></span>
            </h1>
            </div>
            </div>
            <div class="hero-identity"><span class="tiny-cross" aria-hidden="true">+</span><div><p class="hero-name"><?= htmlspecialchars($profile['name']) ?></p><p class="hero-role"><?= htmlspecialchars($profile['role'][$lang] ?? $profile['role']['es']) ?></p><?php if ($currentPosition): ?><a class="current-position" href="<?= htmlspecialchars($urlFor('experience')) ?>" data-section="experience"><i class="status-dot" aria-hidden="true"></i><?= htmlspecialchars($lang === 'es' ? 'Actualmente en' : 'Currently at') ?> <strong><?= htmlspecialchars($currentPosition['company']) ?></strong> <span aria-hidden="true">↗</span></a><?php endif; ?></div></div>
        </div>
        <figure class="hero-portrait">
            <div class="portrait-mat" aria-hidden="true"><span class="portrait-mat-label micro">INDEPENDENT / DEVELOPER</span><span class="portrait-edition">01</span><span class="portrait-mat-bottom micro">PORTFOLIO — VOL. 01</span></div>
            <div class="portrait-surface">
            <div class="portrait-frame"><img src="<?= htmlspecialchars($portrait) ?>" <?php if ($portrait === app_image_path('assets/images/portrait.webp')): ?>srcset="/<?= htmlspecialchars(app_image_path('assets/images/portrait-480.webp')) ?> 480w, /<?= htmlspecialchars($portrait) ?> 784w" sizes="(max-width: 600px) 72vw, (max-width: 1800px) 31vw, 520px"<?php endif; ?> width="784" height="882" fetchpriority="high" alt="<?= htmlspecialchars(sprintf($seoCopy['portrait_alt'], $profile['name'])) ?>"><span class="portrait-index micro">J / A — 01</span><span class="portrait-mark" aria-hidden="true">j.</span></div>
            <a class="portrait-badge" href="<?= htmlspecialchars($urlFor('contact')) ?>" data-section="contact" aria-label="<?= htmlspecialchars($t['nav_contact']) ?>"><span aria-hidden="true">↗</span></a>
            <span class="portrait-film-edge" aria-hidden="true">JACOTO — HUMAN BEHIND THE CODE</span>
            </div>
            <figcaption><span><?= htmlspecialchars($profile['age'][$lang] ?? $profile['age']['es']) ?></span><span><?= htmlspecialchars($t['hero_open_remote']) ?> <i class="status-dot" aria-hidden="true"></i></span></figcaption>
        </figure>
    <div class="hero-sequence" aria-label="<?= htmlspecialchars($editorial['hero_sequence']) ?>">
        <span class="hero-sequence-caption micro"><?= htmlspecialchars($editorial['hero_sequence']) ?></span>
        <ol class="hero-sequence-steps">
            <?php foreach ($editorial['hero_chapters'] as $chapter => $chapterLabel): ?><li<?= $chapter === 0 ? ' aria-current="step"' : '' ?>><span class="micro">0<?= $chapter + 1 ?></span><span><?= htmlspecialchars($chapterLabel) ?></span></li><?php endforeach; ?>
        </ol>
        <button type="button" class="hero-replay micro" hidden aria-label="<?= htmlspecialchars($editorial['hero_replay']) ?>"><span aria-hidden="true">↺</span><span><?= htmlspecialchars($editorial['hero_replay']) ?></span></button>
        <span class="hero-sequence-track" aria-hidden="true"><span></span></span>
    </div>
    </div>
    </div>
    <div class="hero-bottom">
        <a class="scroll-link micro" href="<?= htmlspecialchars($urlFor('projects')) ?>" data-section="projects"><span class="circle-arrow" aria-hidden="true">↓</span><?= htmlspecialchars($editorial['scroll']) ?></a>
        <p class="hero-bio"><?= htmlspecialchars($profile['bio'][$lang] ?? $profile['bio']['es']) ?></p>
        <div class="hero-actions"><a class="text-link" href="cv.php?lang=<?= htmlspecialchars($lang) ?>&print=1" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t['hero_generate_cv_pdf']) ?> <span aria-hidden="true">↗</span></a><a class="text-link secondary" href="<?= htmlspecialchars($profile['cv']) ?>" download="<?= htmlspecialchars($profile['cv_download_name'] ?? 'CV.pdf') ?>"><?= htmlspecialchars($t['hero_download_cv']) ?> <span aria-hidden="true">↓</span></a></div>
    </div>
    <?php if (!empty($t['hero_typewriter'])): ?><p class="hero-signature"><?= htmlspecialchars($t['hero_typewriter']) ?></p><?php endif; ?>
    <div class="specialties" aria-label="<?= htmlspecialchars($editorial['specialties']) ?>">
        <?php foreach ($hw as $card): ?>
            <details class="specialty" data-specialty>
                <summary><?= htmlspecialchars($card['label']) ?><span aria-hidden="true">+</span></summary>
                <div class="specialty-links">
                    <?php foreach ($card['items'] as $item): ?>
                        <?php $itemUrl = str_replace('%lang%', $lang, $item['url'] ?: '#projects'); ?>
                        <a href="<?= htmlspecialchars($itemUrl) ?>" <?= preg_match('~^https?://~i', $itemUrl) ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= htmlspecialchars($item['label']) ?> <span aria-hidden="true">↗</span></a>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</section>
