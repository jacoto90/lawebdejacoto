<?php /** @var array $profile */ /** @var array $t */ ?>
<footer class="site-footer">
    <div class="container footer-wrap">
        <p>&copy; 2026 <?= htmlspecialchars($profile['name']) ?> · <?= htmlspecialchars($t['footer_copy']) ?></p>
        <div>
            <a href="mailto:<?= htmlspecialchars($profile['email']) ?>"><?= htmlspecialchars($profile['email']) ?></a>
        </div>
    </div>
</footer>
