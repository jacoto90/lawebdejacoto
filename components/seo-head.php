<?php
/** @var array $seo */
$seoEscape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
    <title><?= $seoEscape($seo['title']) ?></title>
    <meta name="description" content="<?= $seoEscape($seo['description']) ?>">
    <meta name="author" content="<?= $seoEscape($seo['author']) ?>">
    <meta name="robots" content="<?= $seoEscape($seo['robots']) ?>">
    <link rel="canonical" href="<?= $seoEscape($seo['canonical']) ?>">
    <?php foreach ($seo['alternates'] as $seoLanguage => $seoUrl): ?>
    <link rel="alternate" hreflang="<?= $seoEscape($seoLanguage) ?>" href="<?= $seoEscape($seoUrl) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= $seoEscape($seo['alternates']['es']) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Jacoto">
    <meta property="og:locale" content="<?= $seo['lang'] === 'es' ? 'es_ES' : 'en_GB' ?>">
    <meta property="og:locale:alternate" content="<?= $seo['lang'] === 'es' ? 'en_GB' : 'es_ES' ?>">
    <meta property="og:title" content="<?= $seoEscape($seo['title']) ?>">
    <meta property="og:description" content="<?= $seoEscape($seo['description']) ?>">
    <meta property="og:url" content="<?= $seoEscape($seo['canonical']) ?>">
    <meta property="og:image" content="<?= $seoEscape($seo['image']) ?>">
    <meta property="og:image:type" content="<?= $seoEscape($seo['image_type']) ?>">
    <meta property="og:image:width" content="<?= $seo['dimensions'][0] ?>">
    <meta property="og:image:height" content="<?= $seo['dimensions'][1] ?>">
    <meta property="og:image:alt" content="<?= $seoEscape($seo['image_alt']) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $seoEscape($seo['title']) ?>">
    <meta name="twitter:description" content="<?= $seoEscape($seo['description']) ?>">
    <meta name="twitter:image" content="<?= $seoEscape($seo['image']) ?>">
    <meta name="twitter:image:alt" content="<?= $seoEscape($seo['image_alt']) ?>">
    <script type="application/ld+json"><?= json_encode($seo['graph'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
