<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<section class="section" id="about">
    <div class="container split">
        <div>
            <h3><?= htmlspecialchars($t['about_title']) ?></h3>
            <p><?= htmlspecialchars($t['about_text']) ?></p>
            <h4 class="subsection-title"><?= htmlspecialchars($t['studies_title']) ?></h4>
            <div class="studies-list">
                <?php foreach ($profile['studies'] as $study): ?>
                    <article class="study-item">
                        <span><?= htmlspecialchars($study['period'][$lang] ?? $study['period']['es']) ?></span>
                        <strong><?= htmlspecialchars($study['title'][$lang] ?? $study['title']['es']) ?></strong>
                        <small><?= htmlspecialchars($study['center'][$lang] ?? $study['center']['es']) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="skill-cloud">
            <?php foreach ($profile['skills'] as $skill): ?>
                <span><?= htmlspecialchars($skill) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
