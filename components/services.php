<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<section class="section" id="services">
    <div class="container">
        <h3><?= htmlspecialchars($t['services_title']) ?></h3>
        <div class="cards three">
            <?php foreach ($profile['services'] as $service): ?>
                <article class="card">
                    <h4><?= htmlspecialchars($service['title'][$lang] ?? $service['title']['es']) ?></h4>
                    <p><?= htmlspecialchars($service['desc'][$lang] ?? $service['desc']['es']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
