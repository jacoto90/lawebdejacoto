<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<section class="section" id="projects">
    <div class="container">
        <h3><?= htmlspecialchars($t['projects_title']) ?></h3>
        <div class="cards three">
            <?php foreach ($profile['projects'] as $project): ?>
                <article class="card project-card">
                    <div class="project-logo" aria-hidden="true"><?= htmlspecialchars($project['logo'] ?? 'PR') ?></div>
                    <p class="project-type"><?= htmlspecialchars($project['type'][$lang] ?? $project['type']['es']) ?></p>
                    <h4><?= htmlspecialchars($project['name']) ?></h4>
                    <p><?= htmlspecialchars($project['description'][$lang] ?? $project['description']['es']) ?></p>
                    <a href="<?= htmlspecialchars($project['url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t['projects_visit']) ?></a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
