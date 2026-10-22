<?php
/** @var array $profile */ /** @var array $editorial */
$projectItems = array_values($profile['projects']);
$projectSplit = (int) ceil(count($projectItems) / 2);
$projectLanes = [array_slice($projectItems, 0, $projectSplit, true), array_slice($projectItems, $projectSplit, null, true)];
$projectVisuals = [
    'MB' => ['class' => 'logistics', 'image' => 'assets/images/logistics.webp', 'label' => 'SYSTEMS / .NET / ANGULAR'],
    'JF' => ['class' => 'photography', 'image' => 'assets/images/photography.webp', 'label' => 'PHOTOGRAPHY / DIGITAL EXPERIENCE'],
    'SN' => ['class' => 'commerce', 'image' => 'assets/images/sticnow.webp', 'label' => 'E-COMMERCE / DEVELOPMENT'],
    'DS' => ['class' => 'content', 'image' => '', 'label' => 'WORDPRESS / AI / AUTOMATION'],
    'PS' => ['class' => 'safety', 'image' => 'assets/images/paso-seguro.webp', 'label' => 'IoT / SAAS / LARAVEL / VUE'],
];
?>
<section class="section projects-section" id="projects" aria-labelledby="projectsTitle">
    <div class="container">
        <div class="section-kicker micro"><span>01 / <?= htmlspecialchars($editorial['selected']) ?></span><span>(<?= str_pad((string) count($projectItems), 2, '0', STR_PAD_LEFT) ?>)</span></div>
        <div class="section-heading projects-heading" data-reveal>
            <div><p class="projects-pretitle"><?= htmlspecialchars($editorial['work_motion']) ?></p><h2 class="display-title" id="projectsTitle"><?= htmlspecialchars($t['projects_title']) ?><span class="title-dot">.</span></h2></div>
            <div class="projects-heading-note"><p><?= htmlspecialchars($editorial['work_note']) ?></p></div>
        </div>
        <div class="projects-toolbar" hidden>
            <span class="projects-hint micro"><span aria-hidden="true">↔</span><?= htmlspecialchars($editorial['work_drag']) ?></span>
            <div class="projects-controls">
                <button class="projects-layout-button" type="button" aria-pressed="false" data-grid-label="<?= htmlspecialchars($editorial['work_grid']) ?>" data-carousel-label="<?= htmlspecialchars($editorial['work_carousel']) ?>"><svg viewBox="0 0 20 20" width="15" height="15" fill="none" stroke="currentColor" aria-hidden="true"><rect x="2" y="2" width="6" height="6" rx="1"/><rect x="12" y="2" width="6" height="6" rx="1"/><rect x="2" y="12" width="6" height="6" rx="1"/><rect x="12" y="12" width="6" height="6" rx="1"/></svg><span><?= htmlspecialchars($editorial['work_grid']) ?></span></button>
                <button class="projects-motion-button" type="button" aria-pressed="false" aria-label="<?= htmlspecialchars($editorial['work_pause']) ?>" data-pause-label="<?= htmlspecialchars($editorial['work_pause']) ?>" data-play-label="<?= htmlspecialchars($editorial['work_play']) ?>"><span aria-hidden="true">Ⅱ</span></button>
                <span class="projects-control-divider" aria-hidden="true"></span>
                <button class="projects-arrow" type="button" data-project-direction="-1" aria-label="<?= htmlspecialchars($editorial['work_previous']) ?>">←</button>
                <button class="projects-arrow" type="button" data-project-direction="1" aria-label="<?= htmlspecialchars($editorial['work_next']) ?>">→</button>
            </div>
        </div>
    </div>
    <div class="project-gallery" role="region" aria-label="<?= htmlspecialchars($editorial['work_gallery']) ?>">
        <div class="project-gallery-grid" aria-hidden="true"></div>
        <div class="project-lanes">
            <?php foreach ($projectLanes as $laneIndex => $laneProjects): ?>
                <div class="project-lane" data-lane="<?= $laneIndex ?>">
                    <div class="project-track"><div class="project-set">
                        <?php foreach ($laneProjects as $index => $project): ?>
                            <?php
                            $projectUrl = str_replace('%lang%', $lang, $project['url']);
                            $projectTarget = str_starts_with($projectUrl, '/') && !str_starts_with($projectUrl, '//') ? '' : ' target="_blank" rel="noopener noreferrer"';
                            $visual = $projectVisuals[$project['logo'] ?? ''] ?? ['class' => 'custom', 'image' => '', 'label' => 'DIGITAL / DEVELOPMENT'];
                            ?>
                            <article class="project-card project-<?= htmlspecialchars($visual['class']) ?>" data-project-index="<?= $index ?>" style="--card-order:<?= $index ?>">
                                <div class="project-card-shell">
                                    <a class="project-visual" href="<?= htmlspecialchars($projectUrl) ?>"<?= $projectTarget ?> aria-label="<?= htmlspecialchars($t['projects_visit'] . ': ' . $project['name']) ?>" draggable="false">
                                        <div class="project-visual-top micro"><span><?= htmlspecialchars($visual['label']) ?></span><span>J—<?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span></div>
                                        <?php if ($visual['image'] !== ''): ?>
                                            <div class="project-screen"><div class="browser-chrome" aria-hidden="true"><i></i><i></i><i></i><span><?= htmlspecialchars(parse_url($projectUrl, PHP_URL_HOST) ?: 'lawebdejacoto.com') ?></span></div><img src="<?= htmlspecialchars($visual['image']) ?>" srcset="<?= htmlspecialchars(str_replace('.webp', '-640.webp', $visual['image'])) ?> 640w, <?= htmlspecialchars($visual['image']) ?> 1280w" sizes="(max-width: 600px) 74vw, (max-width: 1000px) 340px, 440px" width="1280" height="900" loading="lazy" decoding="async" draggable="false" alt="<?= htmlspecialchars($editorial['preview'] . ' · ' . $project['name']) ?>"></div>
                                        <?php elseif ($visual['class'] === 'content'): ?>
                                            <div class="content-art" aria-hidden="true"><span class="code-bracket">{</span><div><span class="micro">CONTENT GENERATOR PRO</span><strong>Ideas.<br>Content.<br><em>Impact.</em></strong><span class="ai-cursor">_</span></div><span class="code-bracket">}</span></div>
                                        <?php else: ?>
                                            <div class="custom-art" aria-hidden="true"><?= htmlspecialchars($project['logo'] ?? 'J.') ?></div>
                                        <?php endif; ?>
                                        <span class="project-open" aria-hidden="true">↗</span>
                                    </a>
                                    <div class="project-info"><div><p class="micro project-type"><?= htmlspecialchars($project['type'][$lang] ?? $project['type']['es']) ?></p><h3><a href="<?= htmlspecialchars($projectUrl) ?>"<?= $projectTarget ?> draggable="false"><?= htmlspecialchars($project['name']) ?> <span aria-hidden="true">↗</span></a></h3></div><p><?= htmlspecialchars($project['description'][$lang] ?? $project['description']['es']) ?></p></div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        <?php if ($laneIndex === 1): ?>
                            <article class="project-card project-invitation" style="--card-order:5"><a class="project-card-shell" href="<?= htmlspecialchars($urlFor('contact')) ?>" data-section="contact" draggable="false"><div class="invitation-top micro"><span>NEXT / PROJECT</span><span aria-hidden="true">↗</span></div><div class="invitation-art"><h3><?= nl2br(htmlspecialchars(str_replace('\\n', "\n", $editorial['work_invitation']))) ?></h3></div><div class="invitation-bottom"><p><?= htmlspecialchars($editorial['work_invitation_note']) ?></p><strong><?= htmlspecialchars($editorial['work_invitation_link']) ?> <span aria-hidden="true">↗</span></strong></div></a></article>
                        <?php endif; ?>
                    </div></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="container projects-footer"><span class="micro"><?= str_pad((string) count($projectItems), 2, '0', STR_PAD_LEFT) ?> / <?= htmlspecialchars($editorial['work_index']) ?></span><span class="projects-footer-line" aria-hidden="true"></span><span class="micro">CRAFTED WITH INTENTION</span></div>
</section>
