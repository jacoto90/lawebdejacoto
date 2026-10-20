<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<section class="section experience-section" id="experience" aria-labelledby="experienceTitle">
    <div class="container">
        <div class="section-kicker micro"><span>03 / <?= htmlspecialchars($editorial['experience_label']) ?></span><span>2009 — <?= date('Y') ?></span></div>
        <div class="section-heading" data-reveal><h2 class="display-title" id="experienceTitle"><?= htmlspecialchars($t['experience_title']) ?><span class="title-dot">.</span></h2></div>
        <div class="timeline">
            <?php foreach ($profile['experience'] as $job): ?>
                <article class="timeline-item" data-reveal>
                    <?php $duration = app_experience_duration($job['start_date'] ?? '', $job['end_date'] ?? '', $lang); ?>
                    <p class="timeline-period">
                        <?= htmlspecialchars($job['period'][$lang] ?? $job['period']['es']) ?>
                        <?php if ($duration !== ''): ?>
                            <span class="timeline-duration">· <?= htmlspecialchars($duration) ?></span>
                        <?php endif; ?>
                    </p>
                    <div class="timeline-content">
                    <h3><?= htmlspecialchars($job['company']) ?></h3>
                    <p class="timeline-title"><?= htmlspecialchars($job['title'][$lang] ?? $job['title']['es']) ?></p>
                    <p><?= htmlspecialchars($job['summary'][$lang] ?? $job['summary']['es']) ?></p>
                    <?php if (!empty($job['projects'])): ?>
                        <details class="exp-projects" data-accordion>
                            <summary>
                                <span>+ <?= htmlspecialchars($t['experience_projects']) ?></span>
                                <span class="exp-toggle"><?= htmlspecialchars($t['experience_toggle']) ?></span>
                            </summary>
                            <ul>
                                <?php foreach ($job['projects'] as $project): ?>
                                    <li>
                                        <?= htmlspecialchars($project[$lang] ?? $project['es']) ?>
                                        <?php if (!empty($project['url'])): ?>
                                            <?php
                                            $projectUrl = str_replace('%lang%', $lang, $project['url']);
                                            $projectLinkLabel = $project['url_label'][$lang] ?? $project['url_label']['es'] ?? $projectUrl;
                                            ?>
                                            <a href="<?= htmlspecialchars($projectUrl) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($projectLinkLabel) ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                    <div class="chip-row">
                        <?php foreach ($job['tags'] as $tag): ?>
                            <span class="chip"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
