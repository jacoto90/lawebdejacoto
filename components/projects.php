<?php /** @var array $profile */ /** @var array $editorial */ ?>
<section class="section projects-section" id="projects" aria-labelledby="projectsTitle">
    <div class="container">
        <div class="section-kicker micro"><span>01 / <?= htmlspecialchars($editorial['selected']) ?></span><span>(<?= str_pad((string) count($profile['projects']), 2, '0', STR_PAD_LEFT) ?>)</span></div>
        <div class="section-heading" data-reveal><h2 class="display-title" id="projectsTitle"><?= htmlspecialchars($t['projects_title']) ?><span class="title-dot">.</span></h2><p><?= htmlspecialchars($editorial['work_note']) ?></p></div>
        <div class="project-grid">
            <?php foreach ($profile['projects'] as $index => $project): ?>
                <?php
                $projectUrl = str_replace('%lang%', $lang, $project['url']);
                $projectVisuals = [
                    'MB' => ['class' => 'logistics', 'image' => 'assets/images/logistics.webp', 'label' => 'SYSTEMS / .NET / ANGULAR'],
                    'JF' => ['class' => 'photography', 'image' => 'assets/images/photography.webp', 'label' => 'PHOTOGRAPHY / DIGITAL EXPERIENCE'],
                    'SN' => ['class' => 'commerce', 'image' => 'assets/images/sticnow.webp', 'label' => 'E-COMMERCE / DEVELOPMENT'],
                    'DS' => ['class' => 'content', 'image' => '', 'label' => 'WORDPRESS / AI / AUTOMATION'],
                    'PS' => ['class' => 'safety', 'image' => '', 'label' => 'SOFTWARE / OPERATIONS'],
                ];
                $visual = $projectVisuals[$project['logo'] ?? ''] ?? ['class' => 'custom', 'image' => '', 'label' => 'DIGITAL / DEVELOPMENT'];
                ?>
                <article class="project-card project-<?= htmlspecialchars($visual['class']) ?>" data-reveal>
                    <a class="project-visual" href="<?= htmlspecialchars($projectUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($t['projects_visit'] . ': ' . $project['name']) ?>">
                        <div class="project-visual-top micro"><span><?= htmlspecialchars($visual['label']) ?></span><span>J—<?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span></div>
                        <?php if ($visual['image'] !== ''): ?>
                            <div class="project-screen"><div class="browser-chrome" aria-hidden="true"><i></i><i></i><i></i><span><?= htmlspecialchars(parse_url($projectUrl, PHP_URL_HOST) ?: 'lawebdejacoto.com') ?></span></div><img src="<?= htmlspecialchars($visual['image']) ?>" srcset="<?= htmlspecialchars(str_replace('.webp', '-640.webp', $visual['image'])) ?> 640w, <?= htmlspecialchars($visual['image']) ?> 1280w" sizes="(max-width: 600px) 78vw, <?= $index === 0 ? '(max-width: 1800px) 62vw, 1060px' : '(max-width: 1800px) 38vw, 650px' ?>" width="1280" height="900" loading="lazy" decoding="async" alt="<?= htmlspecialchars($editorial['preview'] . ' · ' . $project['name']) ?>"></div>
                        <?php elseif ($visual['class'] === 'safety'): ?>
                            <div class="safety-art" aria-hidden="true"><div class="safety-orbit"></div><svg viewBox="0 0 100 110" fill="none"><path d="M50 6 88 20v32c0 25-23 43-38 51C35 95 12 77 12 52V20Z" stroke="currentColor" stroke-width="2"/><path d="m32 53 13 13 26-29" stroke="currentColor" stroke-width="5"/></svg><strong>PASO<br>SEGURO.</strong><span>CONTROL · TRAZABILIDAD · SEGURIDAD</span></div>
                        <?php elseif ($visual['class'] === 'content'): ?>
                            <div class="content-art" aria-hidden="true"><span class="code-bracket">{</span><div><span class="micro">CONTENT GENERATOR PRO</span><strong>Ideas.<br>Content.<br><em>Impact.</em></strong><span class="ai-cursor">_</span></div><span class="code-bracket">}</span></div>
                        <?php else: ?>
                            <div class="custom-art" aria-hidden="true"><?= htmlspecialchars($project['logo'] ?? 'J.') ?></div>
                        <?php endif; ?>
                        <span class="project-open"><span><?= htmlspecialchars($t['projects_visit']) ?></span> ↗</span>
                    </a>
                    <div class="project-info"><div><p class="micro project-type"><?= htmlspecialchars($project['type'][$lang] ?? $project['type']['es']) ?></p><h3><a href="<?= htmlspecialchars($projectUrl) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($project['name']) ?> <span aria-hidden="true">↗</span></a></h3></div><p><?= htmlspecialchars($project['description'][$lang] ?? $project['description']['es']) ?></p></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
