<?php
define('APP_BASE_PATH', __DIR__);
require __DIR__ . '/app/env.php';
require __DIR__ . '/app/database.php';
require __DIR__ . '/app/content.php';
require __DIR__ . '/app/seo.php';

$profile = app_public_profile(__DIR__ . '/data/profile.php');
$translations = app_public_translations(__DIR__ . '/data/translations.php');
$heroCards = [];
try {
    if (app_database_is_configured()) {
        $heroCards = app_portfolio_hero_cards(app_db());
    }
} catch (Throwable $e) { $heroCards = []; }
$routeSlugs = [
    'home' => ['es' => '', 'en' => ''],
    'about' => ['es' => 'sobre-mi', 'en' => 'about'],
    'experience' => ['es' => 'experiencia', 'en' => 'experience'],
    'services' => ['es' => 'servicios', 'en' => 'services'],
    'projects' => ['es' => 'proyectos', 'en' => 'projects'],
    'hobby' => ['es' => 'hobbies', 'en' => 'hobbies'],
    'contact' => ['es' => 'hablemos', 'en' => 'contact'],
];

$targetIds = [
    'home' => 'top',
    'about' => 'about',
    'experience' => 'experience',
    'services' => 'services',
    'projects' => 'projects',
    'hobby' => 'hobby',
    'contact' => 'contact',
];

$lang = 'es';
$currentRouteKey = 'home';

$requestPath = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$segments = array_values(array_filter(explode('/', $requestPath), 'strlen'));

if (!empty($segments) && $segments[0] === 'index.php') {
    array_shift($segments);
}

if (!empty($segments) && in_array($segments[0], ['es', 'en'], true)) {
    $lang = $segments[0];
    array_shift($segments);
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'], true)) {
    $lang = $_GET['lang'];
}

if (!isset($translations[$lang])) {
    $lang = 'es';
}

$slug = $segments[0] ?? '';
if ($slug !== '') {
    foreach ($routeSlugs as $key => $langs) {
        if ($slug === $langs['es'] || $slug === $langs['en']) {
            $currentRouteKey = $key;
            break;
        }
    }
}

$urlFor = static function (string $routeKey = 'home', ?string $forceLang = null) use ($routeSlugs, $lang): string {
    $useLang = $forceLang ?? $lang;
    $prefix = $useLang === 'en' ? '/en' : '';
    $slug = $routeSlugs[$routeKey][$useLang] ?? '';
    return $slug === '' ? ($prefix === '' ? '/' : $prefix) : $prefix . '/' . $slug;
};

$t = $translations[$lang];
$seo = app_seo_page($currentRouteKey, $lang, $profile);
$seoCopy = (require __DIR__ . '/data/seo.php')[$lang];
$editorial = (require __DIR__ . '/data/editorial.php')[$lang];
foreach ($editorial as $key => $value) {
    if (is_array($value)) {
        foreach ($value as $part => $text) $editorial[$key][$part] = $t['editorial_' . $key . '_' . $part] ?? $text;
    } else {
        $editorial[$key] = $t['editorial_' . $key] ?? $value;
    }
}
$assetVersion = static function (string $path): string {
    $path = app_image_path($path);
    return '/' . $path . '?v=' . filemtime(__DIR__ . '/' . $path);
};
$imageFor = static function (string $path): string {
    $optimized = [
        'images/perfil_joseangel_linkedin.jpg' => 'assets/images/portrait.webp',
        'images/joseangel_golf.png' => 'assets/images/golf.webp',
    ];
    return app_image_path($optimized[ltrim($path, '/')] ?? $path);
};
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <?php require __DIR__ . '/components/seo-head.php'; ?>
    <meta name="theme-color" content="#f2f0e9">
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="/assets/fonts/vina-sans-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/manrope-latin.woff2" as="font" type="font/woff2" crossorigin>
    <script>try{const t=localStorage.getItem('lawebdejacoto_theme');document.documentElement.dataset.theme=t==='dark'?'dark':'light'}catch(e){}</script>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion('assets/css/portfolio.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion('assets/css/projects.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetVersion('assets/css/hero-cinema.css')) ?>">
    <noscript><style>@media(max-width:900px){.site-header{position:relative}.nav-wrap{flex-wrap:wrap}.main-nav{position:static;visibility:visible;opacity:1;pointer-events:auto;transform:none;height:auto;width:100%;order:3;padding:10px 0 20px;flex-direction:row;flex-wrap:wrap;gap:12px}.main-nav>a{font:600 12px Manrope,sans-serif;border:0}.menu-btn,.theme-toggle{display:none}}</style></noscript>
</head>
<body data-scroll-target="<?= htmlspecialchars($targetIds[$currentRouteKey] ?? 'top') ?>" data-language="<?= htmlspecialchars($lang) ?>">
<a class="skip-link" href="#main"><?= htmlspecialchars($editorial['skip']) ?></a>
<?php require __DIR__ . '/components/header.php'; ?>
<main id="main" tabindex="-1">
    <?php require __DIR__ . '/components/hero.php'; ?>
    <?php require __DIR__ . '/components/projects.php'; ?>
    <?php require __DIR__ . '/components/about.php'; ?>
    <?php require __DIR__ . '/components/experience.php'; ?>
    <?php require __DIR__ . '/components/services.php'; ?>
    <?php require __DIR__ . '/components/hobby.php'; ?>
    <?php require __DIR__ . '/components/contact.php'; ?>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
<script src="<?= htmlspecialchars($assetVersion('assets/js/projects.js')) ?>" defer></script>
<script src="<?= htmlspecialchars($assetVersion('assets/js/portfolio.js')) ?>" defer></script>
<script src="<?= htmlspecialchars($assetVersion('assets/js/hero-cinema.js')) ?>" defer></script>
</body>
</html>
