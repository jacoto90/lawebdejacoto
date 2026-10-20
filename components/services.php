<?php /** @var array $profile */ /** @var array $editorial */ ?>
<section class="section services-section" id="services" aria-labelledby="servicesTitle">
    <div class="container">
        <div class="section-kicker micro"><span>04 / <?= htmlspecialchars($t['services_title']) ?></span><span><?= htmlspecialchars($editorial['services_label']) ?></span></div>
        <div class="section-heading" data-reveal><h2 class="display-title" id="servicesTitle"><?= htmlspecialchars($t['services_title']) ?><span class="title-dot">.</span></h2><p><?= htmlspecialchars($editorial['intro']) ?></p></div>
        <div class="services-list">
            <?php foreach ($profile['services'] as $index => $service): ?>
                <article class="service-row" data-reveal><span class="micro service-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= htmlspecialchars($service['title'][$lang] ?? $service['title']['es']) ?></h3><p><?= htmlspecialchars($service['desc'][$lang] ?? $service['desc']['es']) ?></p><a class="service-arrow" href="<?= htmlspecialchars($urlFor('contact')) ?>" data-section="contact" aria-label="<?= htmlspecialchars($t['nav_contact'] . ': ' . ($service['title'][$lang] ?? $service['title']['es'])) ?>">↗</a></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
