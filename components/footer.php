<?php /** @var array $profile */ /** @var array $editorial */ ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-top micro"><span><?= htmlspecialchars($profile['location'][$lang] ?? $profile['location']['es']) ?></span><span><?= htmlspecialchars($editorial['signature']) ?></span><a href="<?= htmlspecialchars($urlFor('home')) ?>" data-section="top"><?= htmlspecialchars($editorial['back_top']) ?> ↑</a></div>
        <a class="footer-wordmark" href="<?= htmlspecialchars($urlFor('home')) ?>" data-section="top" aria-label="Jacoto · <?= htmlspecialchars($editorial['back_top']) ?>">JACOTO<span aria-hidden="true">✳</span></a>
        <div class="footer-bottom micro"><span>© <?= date('Y') ?> JACOTO</span><span><?= htmlspecialchars($t['footer_copy']) ?></span><span><?= htmlspecialchars($editorial['edition']) ?></span></div>
    </div>
</footer>
