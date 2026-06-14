<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ /** @var callable $urlFor */ /** @var array $heroCards */ ?>
<?php
$hw = $heroCards ?: [];
$typewriterText = $t['hero_typewriter'] ?? 'Hola, soy José Angel. Desarrollo soluciones, aplicaciones top y webs que venden.';
?>
<section class="hero" id="top">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow"><?= htmlspecialchars($t['hero_eyebrow']) ?></p>
            <?php
            $nameParts = explode(' ', $profile['name']);
            $firstLine = implode(' ', array_slice($nameParts, 0, 2));
            $secondLine = implode(' ', array_slice($nameParts, 2));
            ?>
            <h1>
                <span class="hero-name-line hero-name-line-1"><?= htmlspecialchars($firstLine) ?></span>
                <span class="hero-name-line hero-name-line-2"><?= htmlspecialchars($secondLine) ?></span>
            </h1>
            <p class="hero-role"><?= htmlspecialchars($profile['role'][$lang] ?? $profile['role']['es']) ?></p>
            <p class="hero-text"><?= htmlspecialchars($profile['bio'][$lang] ?? $profile['bio']['es']) ?></p>
            <div class="hero-actions">
                <a href="<?= htmlspecialchars($urlFor('projects')) ?>" class="btn btn-primary"><?= htmlspecialchars($t['hero_view_projects']) ?></a>
                <a href="cv.php?lang=<?= htmlspecialchars($lang) ?>&print=1" class="btn btn-ghost" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t['hero_generate_cv_pdf'] ?? 'Generar PDF') ?></a>
            </div>
            <div class="highlight-grid">
                <?php foreach ($profile['highlights'] as $highlight): ?>
                    <article class="highlight-card">
                        <strong><?= htmlspecialchars($highlight['metric']) ?></strong>
                        <span><?= htmlspecialchars($highlight['label'][$lang] ?? $highlight['label']['es']) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <aside class="profile-card">
            <div class="profile-photo-wrap" id="profilePhotoWrap">
                <?php $cvPhoto = $profile['cv_photo'] ?? 'images/perfil_joseangel_linkedin.jpg'; ?>
                <img src="<?= htmlspecialchars($cvPhoto) ?>" alt="Jose Angel profile portrait" class="profile-photo-main profile-photo-single">
                <div class="hero-type-signature" aria-live="polite">
                    <span class="type-line" data-typewriter="<?= htmlspecialchars($typewriterText) ?>"><?= htmlspecialchars($typewriterText) ?></span>
                </div>
                <div class="hero-word-orbit" aria-label="Explorar especialidades">
                    <?php
                    if (empty($hw)) {
                        $defaultCards = [
                            ['name' => 'fullstack', 'label' => 'Full Stack', 'items' => [['label' => 'Laravel / PHP', 'url' => '#experience'], ['label' => '.NET / C#', 'url' => '#experience'], ['label' => 'Angular', 'url' => '#experience'], ['label' => 'SQL / APIs', 'url' => '#services']]],
                            ['name' => 'erp', 'label' => 'ERP', 'items' => [['label' => 'Odoo', 'url' => '#experience'], ['label' => 'SAP', 'url' => '#experience'], ['label' => 'Automatizaciones', 'url' => '#services'], ['label' => 'Procesos internos', 'url' => '#services']]],
                            ['name' => 'ecommerce', 'label' => 'E-commerce', 'items' => [['label' => 'Shopify', 'url' => '#experience'], ['label' => 'SticNow', 'url' => 'https://sticnow.com'], ['label' => 'Checkout', 'url' => '#services'], ['label' => 'Conversión', 'url' => '#projects']]],
                            ['name' => 'logistica', 'label' => 'Logística', 'items' => [['label' => 'Paso Seguro', 'url' => 'https://pasoseguro.pro'], ['label' => 'Proyecto Logístico', 'url' => '/proyecto-logistica/index.php?lang=' . htmlspecialchars($lang)], ['label' => 'Trazabilidad', 'url' => '#projects'], ['label' => 'Dashboards', 'url' => '#services']]],
                            ['name' => 'producto', 'label' => 'Producto', 'items' => [['label' => 'Jacoto Fotografía', 'url' => 'https://jacotofotografia.com'], ['label' => 'Landings', 'url' => '#services'], ['label' => 'SEO técnico', 'url' => '#projects'], ['label' => 'Analítica', 'url' => '#contact']]],
                        ];
                        $hw = $defaultCards;
                    }
                    ?>
                    <?php foreach ($hw as $ci => $card): ?>
                        <?php
                        $itemsStr = implode('|', array_map(static fn($it) => htmlspecialchars(($it['label'] ?? '') . '::' . ($it['url'] ?? '#projects')), $card['items']));
                        ?>
                        <button class="word word-<?= $ci + 1 ?>" type="button" data-hero-stack="<?= $itemsStr ?>"><?= htmlspecialchars($card['label']) ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="hero-stack-burst" id="heroStackBurst" aria-live="polite"></div>
            </div>
            <h2><?= htmlspecialchars($profile['name']) ?></h2>
            <p><?= htmlspecialchars($profile['location'][$lang] ?? $profile['location']['es']) ?></p>
            <div class="chip-row">
                <span class="chip"><?= htmlspecialchars($profile['age'][$lang] ?? $profile['age']['es']) ?></span>
                <span class="chip"><?= htmlspecialchars($t['hero_open_remote']) ?></span>
            </div>
        </aside>
    </div>
</section>
