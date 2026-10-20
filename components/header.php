<?php /** @var array $t */ /** @var array $editorial */ /** @var callable $urlFor */ ?>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= htmlspecialchars($urlFor('home')) ?>" data-section="top" aria-label="Jacoto · <?= htmlspecialchars($editorial['back_top']) ?>">jacoto<span class="brand-dot" aria-hidden="true">✳</span></a>
        <nav class="main-nav" id="mainNav" aria-label="<?= htmlspecialchars($editorial['navigation']) ?>">
            <?php foreach (['projects', 'about', 'experience', 'services', 'hobby', 'contact'] as $navKey): ?>
                <a href="<?= htmlspecialchars($urlFor($navKey)) ?>" data-section="<?= htmlspecialchars($navKey) ?>" <?= $navKey === $currentRouteKey ? 'aria-current="location"' : '' ?>>
                    <?= htmlspecialchars($t['nav_' . $navKey]) ?><?php if ($navKey === 'projects'): ?><sup><?= count($profile['projects']) ?></sup><?php endif; ?><?php if ($navKey === 'contact'): ?><span aria-hidden="true"> ↗</span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="nav-tools">
            <div class="lang-switch" aria-label="Idioma / Language">
                <a href="<?= htmlspecialchars($urlFor($currentRouteKey, 'es')) ?>" lang="es" data-language-link="es" <?= $lang === 'es' ? 'aria-current="true"' : '' ?>>ES</a>
                <span aria-hidden="true">/</span>
                <a href="<?= htmlspecialchars($urlFor($currentRouteKey, 'en')) ?>" lang="en" data-language-link="en" <?= $lang === 'en' ? 'aria-current="true"' : '' ?>>EN</a>
            </div>
            <button class="theme-toggle" id="themeToggle" type="button" aria-label="<?= htmlspecialchars($editorial['theme']) ?>" aria-pressed="false" title="<?= htmlspecialchars($editorial['theme']) ?>">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.5"/><path d="M12 4a8 8 0 0 1 0 16Z" fill="currentColor"/></svg>
            </button>
            <button class="menu-btn" type="button" id="menuBtn" aria-expanded="false" aria-controls="mainNav" data-open-label="<?= htmlspecialchars($editorial['menu']) ?>" data-close-label="<?= htmlspecialchars($editorial['close']) ?>"><span><?= htmlspecialchars($editorial['menu']) ?></span><i aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="reading-progress" aria-hidden="true"></div>
</header>
