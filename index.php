<?php
$profile = require __DIR__ . '/data/profile.php';
$translations = require __DIR__ . '/data/translations.php';
$lang = $_GET['lang'] ?? 'es';
if (!isset($translations[$lang])) {
    $lang = 'es';
}
$t = $translations[$lang];
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile['name']) ?> · Portfolio</title>
    <meta name="description" content="<?= htmlspecialchars($t['meta_description']) ?>">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
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
