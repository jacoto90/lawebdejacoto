<?php /** @var array $profile */ /** @var array $editorial */ ?>
<section class="section hobby-section" id="hobby" aria-labelledby="hobbyTitle">
    <div class="container">
        <div class="section-kicker micro"><span>05 / <?= htmlspecialchars($t['hobby_title']) ?></span><span><?= htmlspecialchars($editorial['hobby_label']) ?></span></div>
        <div class="hobby-layout">
            <figure class="hobby-media" data-reveal><img src="<?= htmlspecialchars($imageFor($profile['golf_photo'])) ?>" <?php if ($imageFor($profile['golf_photo']) === 'assets/images/golf.webp'): ?>srcset="/assets/images/golf-480.webp 480w, /assets/images/golf.webp 960w" sizes="(max-width: 600px) 75vw, (max-width: 1800px) 40vw, 690px"<?php endif; ?> width="960" height="1200" loading="lazy" decoding="async" alt="<?= htmlspecialchars($editorial['golf_alt']) ?>"><figcaption class="micro">FOCUS / PRECISION / BALANCE</figcaption></figure>
            <div class="hobby-content" data-reveal><span class="hobby-star" aria-hidden="true">✳</span><h2 class="display-title" id="hobbyTitle"><?= nl2br(htmlspecialchars(str_replace('\\n', "\n", $editorial['hobby_statement']))) ?></h2><h3><?= htmlspecialchars($profile['hobby']['title'][$lang] ?? $profile['hobby']['title']['es']) ?></h3><p><?= htmlspecialchars($profile['hobby']['text'][$lang] ?? $profile['hobby']['text']['es']) ?></p></div>
        </div>
    </div>
</section>
