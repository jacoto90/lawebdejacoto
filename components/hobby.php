<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<section class="section" id="hobby">
    <div class="container hobby-wrap">
        <div class="hobby-content">
            <h3><?= htmlspecialchars($t['hobby_title']) ?></h3>
            <h4><?= htmlspecialchars($profile['hobby']['title'][$lang] ?? $profile['hobby']['title']['es']) ?></h4>
            <p><?= htmlspecialchars($profile['hobby']['text'][$lang] ?? $profile['hobby']['text']['es']) ?></p>
        </div>
        <figure class="hobby-media">
            <img src="<?= htmlspecialchars($profile['golf_photo']) ?>" alt="Jose Angel playing golf">
        </figure>
    </div>
</section>
