<?php /** @var array $profile */ /** @var array $editorial */ ?>
<section class="section about-section" id="about" aria-labelledby="aboutTitle">
    <div class="container">
        <div class="section-kicker micro"><span>02 / <?= htmlspecialchars($t['about_title']) ?></span><span><?= htmlspecialchars($editorial['about_label']) ?></span></div>
        <div class="about-intro" data-reveal><span class="about-symbol" aria-hidden="true">↘</span><div><h2 id="aboutTitle"><?= nl2br(htmlspecialchars(str_replace('\\n', "\n", $editorial['about_heading']))) ?></h2><p><?= htmlspecialchars($t['about_text']) ?></p></div></div>
        <div class="highlight-grid">
            <?php foreach ($profile['highlights'] as $highlight): ?><div class="highlight-card" data-reveal><strong><?= htmlspecialchars($highlight['metric']) ?></strong><span class="micro"><?= htmlspecialchars($highlight['label'][$lang] ?? $highlight['label']['es']) ?></span></div><?php endforeach; ?>
        </div>
        <div class="about-details">
            <div class="education"><h3 class="micro"><?= htmlspecialchars($t['studies_title']) ?></h3><div class="studies-list"><?php foreach ($profile['studies'] as $study): ?><article class="study-item"><span class="micro"><?= htmlspecialchars($study['period'][$lang] ?? $study['period']['es']) ?></span><h4><?= htmlspecialchars($study['title'][$lang] ?? $study['title']['es']) ?></h4><p><?= htmlspecialchars($study['center'][$lang] ?? $study['center']['es']) ?></p></article><?php endforeach; ?></div></div>
            <div><h3 class="micro"><?= htmlspecialchars($editorial['stack']) ?></h3><div class="skill-cloud"><?php foreach ($profile['skills'] as $skill): ?><?php if ($skill === 'Python / Odoo'): ?><button type="button" class="skill-chip" data-admin-entry><?= htmlspecialchars($skill) ?></button><?php else: ?><span class="skill-chip"><?= htmlspecialchars($skill) ?></span><?php endif; ?><?php endforeach; ?></div></div>
        </div>
    </div>
</section>
