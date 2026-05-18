<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ /** @var callable $urlFor */ /** @var string $currentRouteKey */ ?>
<header class="site-header">
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="container nav-wrap">
        <a class="brand" href="<?= htmlspecialchars($urlFor('home')) ?>">JACOTO</a>
        <button class="menu-btn" type="button" id="menuBtn" aria-label="menu">Menu</button>
        <nav class="main-nav" id="mainNav">
            <a href="<?= htmlspecialchars($urlFor('about')) ?>"><?= htmlspecialchars($t['nav_about']) ?></a>
            <a href="<?= htmlspecialchars($urlFor('experience')) ?>"><?= htmlspecialchars($t['nav_experience']) ?></a>
            <a href="<?= htmlspecialchars($urlFor('services')) ?>"><?= htmlspecialchars($t['nav_services']) ?></a>
            <a href="<?= htmlspecialchars($urlFor('projects')) ?>"><?= htmlspecialchars($t['nav_projects']) ?></a>
            <a href="<?= htmlspecialchars($urlFor('hobby')) ?>"><?= htmlspecialchars($t['nav_hobby']) ?></a>
            <a href="<?= htmlspecialchars($urlFor('contact')) ?>" class="nav-cta"><?= htmlspecialchars($t['nav_contact']) ?></a>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">☀️</button>
            <div class="lang-switch">
                <a href="<?= htmlspecialchars($urlFor($currentRouteKey, 'es')) ?>" class="<?= $lang === 'es' ? 'active' : '' ?>">ES</a>
                <a href="<?= htmlspecialchars($urlFor($currentRouteKey, 'en')) ?>" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </nav>
    </div>
</header>
