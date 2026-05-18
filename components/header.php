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
            <div class="lang-switch">
                <div class="theme-segment" id="themeSegment" role="radiogroup" aria-label="Theme">
                    <button class="theme-opt" data-theme-val="light" aria-label="Light mode" role="radio" aria-checked="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <button class="theme-opt" data-theme-val="dark" aria-label="Dark mode" role="radio" aria-checked="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                </div>
                <a href="<?= htmlspecialchars($urlFor($currentRouteKey, 'es')) ?>" class="<?= $lang === 'es' ? 'active' : '' ?>">ES</a>
                <a href="<?= htmlspecialchars($urlFor($currentRouteKey, 'en')) ?>" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </nav>
    </div>
</header>
