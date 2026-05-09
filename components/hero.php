<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ /** @var callable $urlFor */ ?>
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
                <a href="<?= htmlspecialchars($profile['cv']) ?>" class="btn btn-ghost" download="<?= htmlspecialchars($profile['cv_download_name']) ?>"><?= htmlspecialchars($t['hero_download_cv']) ?></a>
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
                <img src="<?= htmlspecialchars($profile['photo']) ?>" alt="Profile photo" class="profile-photo-main">
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
