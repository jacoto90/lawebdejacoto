<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<header class="site-header">
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="container nav-wrap">
        <a class="brand" href="#top">JACOTO</a>
        <button class="menu-btn" type="button" id="menuBtn" aria-label="menu">Menu</button>
        <nav class="main-nav" id="mainNav">
            <a href="#about"><?= htmlspecialchars($t['nav_about']) ?></a>
            <a href="#experience"><?= htmlspecialchars($t['nav_experience']) ?></a>
            <a href="#services"><?= htmlspecialchars($t['nav_services']) ?></a>
            <a href="#projects"><?= htmlspecialchars($t['nav_projects']) ?></a>
            <a href="#hobby"><?= htmlspecialchars($t['nav_hobby']) ?></a>
            <a href="#contact" class="nav-cta"><?= htmlspecialchars($t['nav_contact']) ?></a>
            <div class="lang-switch">
                <a href="?lang=es" class="<?= $lang === 'es' ? 'active' : '' ?>">ES</a>
                <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </nav>
    </div>
</header>
