<?php
define('APP_BASE_PATH', __DIR__);
require __DIR__ . '/app/env.php';
require __DIR__ . '/app/database.php';
require __DIR__ . '/app/content.php';

$profile = app_public_profile(__DIR__ . '/data/profile.php');
$translations = app_public_translations(__DIR__ . '/data/translations.php');
$heroCards = [];
try { $pdo = app_db(); $heroCards = app_portfolio_hero_cards($pdo); } catch (Throwable $e) { $heroCards = []; }
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
$heroCards = [];
try { $pdo = app_db(); $heroCards = app_portfolio_hero_cards($pdo); } catch (Throwable $e) { $heroCards = []; }
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title><?= htmlspecialchars($profile['name']) ?> · Portfolio</title>
    <meta name="description" content="<?= htmlspecialchars($t['meta_description']) ?>">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body data-scroll-target="<?= htmlspecialchars($targetIds[$currentRouteKey] ?? 'top') ?>">
<div id="pageLoader" class="page-loader" aria-hidden="true">
    <div class="loader-card">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>
<?php require __DIR__ . '/components/header.php'; ?>
<main>
    <?php require __DIR__ . '/components/hero.php'; ?>
    <?php require __DIR__ . '/components/about.php'; ?>
    <?php require __DIR__ . '/components/experience.php'; ?>
    <?php require __DIR__ . '/components/services.php'; ?>
    <?php require __DIR__ . '/components/projects.php'; ?>
    <?php require __DIR__ . '/components/hobby.php'; ?>
    <?php require __DIR__ . '/components/contact.php'; ?>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
