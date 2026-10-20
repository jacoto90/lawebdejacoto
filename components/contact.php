<?php /** @var array $profile */ /** @var array $editorial */ ?>
<section class="section contact-section" id="contact" aria-labelledby="contactTitle">
    <div class="container">
        <div class="section-kicker micro"><span>06 / <?= htmlspecialchars($t['nav_contact']) ?></span><span><?= htmlspecialchars($editorial['contact_label']) ?></span></div>
        <a class="contact-headline" href="mailto:<?= htmlspecialchars($profile['email']) ?>" data-reveal><h2 id="contactTitle"><?= nl2br(htmlspecialchars(str_replace('\\n', "\n", $editorial['contact_heading']))) ?></h2><span aria-hidden="true">↗</span></a>
        <div class="contact-bottom"><div><h3><?= htmlspecialchars($t['contact_title']) ?></h3><p><?= htmlspecialchars($t['contact_text']) ?></p></div><div class="contact-links"><a class="text-link" href="mailto:<?= htmlspecialchars($profile['email']) ?>"><?= htmlspecialchars($profile['email']) ?> <span aria-hidden="true">↗</span></a><a class="text-link" href="tel:<?= htmlspecialchars(preg_replace('/[^+0-9]/', '', $profile['phone'])) ?>"><?= htmlspecialchars($profile['phone']) ?> <span aria-hidden="true">↗</span></a><a class="text-link" href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t['contact_linkedin']) ?> <span aria-hidden="true">↗</span></a></div></div>
    </div>
</section>
