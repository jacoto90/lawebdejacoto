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
    <div class="hero-stage">
        <div class="hero-title-wrap">
            <h1 class="hero-title" id="heroTitle"><span><?= htmlspecialchars($editorial['headline'][0]) ?></span><span class="hero-middle"><?= htmlspecialchars($editorial['headline'][1]) ?><svg class="hero-asterisk" viewBox="0 0 100 100" fill="none" aria-hidden="true"><path d="M50 0v100M0 50h100M14.6 14.6l70.8 70.8M14.6 85.4l70.8-70.8" stroke="currentColor" stroke-width="15"/></svg></span><span><?= htmlspecialchars($editorial['headline'][2]) ?></span></h1>
            <div class="hero-identity"><span class="tiny-cross" aria-hidden="true">+</span><div><p class="hero-name"><?= htmlspecialchars($profile['name']) ?></p><p class="hero-role"><?= htmlspecialchars($profile['role'][$lang] ?? $profile['role']['es']) ?></p><?php if ($currentPosition): ?><a class="current-position" href="<?= htmlspecialchars($urlFor('experience')) ?>" data-section="experience"><i class="status-dot" aria-hidden="true"></i><?= htmlspecialchars($lang === 'es' ? 'Actualmente en' : 'Currently at') ?> <strong><?= htmlspecialchars($currentPosition['company']) ?></strong> <span aria-hidden="true">↗</span></a><?php endif; ?></div></div>
        </div>
        <figure class="hero-portrait">
            <div class="portrait-frame"><img src="<?= htmlspecialchars($portrait) ?>" <?php if ($portrait === 'assets/images/portrait.webp'): ?>srcset="/assets/images/portrait-480.webp 480w, /assets/images/portrait.webp 784w" sizes="(max-width: 600px) 72vw, (max-width: 1800px) 31vw, 520px"<?php endif; ?> width="784" height="882" fetchpriority="high" alt="<?= htmlspecialchars($editorial['portrait'] . ' ' . $profile['name']) ?>"><span class="portrait-index micro">J / A — 01</span><span class="portrait-mark" aria-hidden="true">j.</span></div>
            <figcaption><span><?= htmlspecialchars($profile['age'][$lang] ?? $profile['age']['es']) ?></span><span><?= htmlspecialchars($t['hero_open_remote']) ?> <i class="status-dot" aria-hidden="true"></i></span></figcaption>
            <a class="portrait-badge" href="<?= htmlspecialchars($urlFor('contact')) ?>" data-section="contact" aria-label="<?= htmlspecialchars($t['nav_contact']) ?>"><span aria-hidden="true">↗</span></a>
        </figure>
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
