<?php /** @var array $profile */ /** @var array $t */ /** @var string $lang */ ?>
<section class="section" id="experience">
    <div class="container">
        <h3><?= htmlspecialchars($t['experience_title']) ?></h3>
        <div class="timeline">
            <?php foreach ($profile['experience'] as $job): ?>
                <article class="timeline-item">
                    <p class="timeline-period"><?= htmlspecialchars($job['period'][$lang] ?? $job['period']['es']) ?></p>
                    <h4><?= htmlspecialchars($job['company']) ?></h4>
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
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
