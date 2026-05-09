<?php /** @var array $profile */ /** @var array $t */ ?>
<section class="section" id="contact">
    <div class="container contact-box">
        <h3><?= htmlspecialchars($t['contact_title']) ?></h3>
        <p><?= htmlspecialchars($t['contact_text']) ?></p>
        <div class="hero-actions">
            <a href="mailto:<?= htmlspecialchars($profile['email']) ?>" class="btn btn-primary"><?= htmlspecialchars($t['contact_email']) ?></a>
            <a href="<?= htmlspecialchars($profile['linkedin']) ?>" class="btn btn-ghost" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t['contact_linkedin']) ?></a>
        </div>
        <div class="chip-row" style="justify-content:center; margin-top: 0.8rem;">
            <span class="chip"><?= htmlspecialchars($profile['phone']) ?></span>
            <span class="chip"><?= htmlspecialchars($profile['email']) ?></span>
        </div>
    </div>
</section>
