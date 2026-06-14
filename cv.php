<?php
define('APP_BASE_PATH', __DIR__);
require __DIR__ . '/app/env.php';
require __DIR__ . '/app/database.php';
require __DIR__ . '/app/content.php';

$profile = app_public_profile(__DIR__ . '/data/profile.php');
$translations = app_public_translations(__DIR__ . '/data/translations.php');

$lang = $_GET['lang'] ?? 'es';
if (!in_array($lang, ['es', 'en'], true)) {
    $lang = 'es';
}

$t = $translations[$lang] ?? $translations['es'];
$print = isset($_GET['print']);

function cv_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cv_text(array $value, string $lang): string
{
    return (string) ($value[$lang] ?? $value['es'] ?? '');
}

function cv_first_words(string $text, int $limit = 28): string
{
    $words = preg_split('/\s+/', trim($text));
    if (!$words || count($words) <= $limit) {
        return $text;
    }

    return implode(' ', array_slice($words, 0, $limit)) . '...';
}

function cv_project_url(string $url, string $lang): string
{
    $url = str_replace('%lang%', $lang, trim($url));
    if ($url === '') {
        return 'https://lawebdejacoto.com/proyecto-logistica/';
    }

    if (preg_match('/^https?:\/\//', $url)) {
        return $url;
    }

    if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}(\/.*)?$/i', $url)) {
        return 'https://' . $url;
    }

    return 'https://lawebdejacoto.com/' . ltrim($url, '/');
}

function cv_clean_url_label(string $url): string
{
    return str_replace(['https://', 'http://'], '', $url);
}

function cv_phone_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^+0-9]/', '', $phone);
}

$labels = [
    'es' => [
        'title' => 'Curriculum generado',
        'portfolio' => 'lawebdejacoto.com - portfolio',
        'stack' => 'Stack técnico',
        'profile' => 'Perfil profesional',
        'objective' => 'Objetivo profesional',
        'education' => 'Formación académica',
        'experience' => 'Experiencia laboral tech',
        'projects' => 'Proyectos destacados',
        'services' => 'Servicios / enfoque',
        'contact' => '+ Datos',
        'languages' => 'Idiomas',
        'english_b2' => 'Inglés B2',
        'driving_license' => 'Carnet de conducir B',
        'own_vehicle' => 'Vehículo propio',
        'phone' => 'Teléfono',
        'print' => 'Guardar como PDF',
        'back' => 'Volver al portfolio',
        'generated' => 'Generado desde los datos actuales del portfolio',
        'continued' => 'continuación',
    ],
    'en' => [
        'title' => 'Generated resume',
        'portfolio' => 'lawebdejacoto.com - portfolio',
        'stack' => 'Technical stack',
        'profile' => 'Professional profile',
        'objective' => 'Professional objective',
        'education' => 'Education',
        'experience' => 'Tech experience',
        'projects' => 'Featured projects',
        'services' => 'Services / focus',
        'contact' => '+ Details',
        'languages' => 'Languages',
        'english_b2' => 'English B2',
        'driving_license' => 'Driving licence B',
        'own_vehicle' => 'Own vehicle',
        'phone' => 'Phone',
        'print' => 'Save as PDF',
        'back' => 'Back to portfolio',
        'generated' => 'Generated from current portfolio data',
        'continued' => 'continued',
    ],
][$lang];

$objectiveItems = [];
foreach (($profile['services'] ?? []) as $service) {
    $objectiveItems[] = cv_text($service['title'], $lang);
}
if ($objectiveItems === []) {
    $objectiveItems = [cv_text($profile['role'], $lang)];
}

$skillDetails = $profile['skill_details'] ?? [];
if ($skillDetails === []) {
    $defaultYears = function_exists('app_default_skill_years') ? app_default_skill_years() : [];
    foreach (($profile['skills'] ?? []) as $skill) {
        $skillDetails[] = ['name' => $skill, 'years_label' => $defaultYears[$skill] ?? ''];
    }
}

$firstPageJobs = array_slice(($profile['experience'] ?? []), 0, 2);
$remainingJobs = array_slice(($profile['experience'] ?? []), 2);
$cvPhoto = $profile['cv_photo'] ?? $profile['photo'];
?>
<!DOCTYPE html>
<html lang="<?= cv_e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= cv_e($profile['name']) ?> · <?= cv_e($labels['title']) ?></title>
    <style>
        :root {
            --navy: #0d2d46;
            --navy-2: #123d5f;
            --ink: #202428;
            --muted: #5f6870;
            --line: #cfd7dd;
            --paper: #ffffff;
            --soft: #f5f7f8;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            margin: 0;
            background: #dfe5e9;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.38;
        }

        .cv-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.8rem;
            background: rgba(13, 45, 70, 0.94);
        }

        .cv-toolbar a,
        .cv-toolbar button {
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            background: #ffffff;
            color: var(--navy);
            padding: 0.65rem 0.95rem;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .cv-page {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            background: var(--paper);
            box-shadow: 0 18px 42px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .cv-page + .cv-page { margin-top: 18px; }

        .cv-page-continuation {
            padding: 0;
        }

        .cv-page-continuation .cv-main-inner {
            padding: 8mm 9mm;
        }

        .cv-page-continuation .cv-section {
            margin-bottom: 3.4mm;
        }

        .cv-page-continuation .cv-section-title {
            margin-bottom: 2.2mm;
            padding: 2mm 3mm;
        }

        .cv-page-continuation .cv-job {
            margin-bottom: 1.8mm;
            padding: 1.3mm 0 1.6mm;
        }

        .cv-page-continuation .cv-job h3 {
            margin-bottom: 0.6mm;
        }

        .cv-page-continuation .cv-job-title {
            margin-bottom: 0.8mm;
        }

        .cv-page-continuation .cv-job li {
            margin-bottom: 0.45mm;
        }

        .cv-page-continuation .cv-project-row {
            padding: 1.1mm 0;
            font-size: 10.5px;
        }

        .cv-shell {
            display: grid;
            grid-template-columns: 52mm 1fr;
            min-height: 297mm;
        }

        .cv-side {
            border-right: 2px solid #254a65;
            background: #f0f2ef;
        }

        .cv-photo {
            width: 100%;
            height: 52mm;
            object-fit: cover;
            display: block;
            border-bottom: 5px solid var(--navy);
        }

        .cv-side-inner { padding: 7mm 5mm; }

        .cv-badge,
        .cv-side-title {
            background: var(--navy);
            color: #ffffff;
            text-align: center;
            padding: 2.8mm 3mm;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin: 5mm auto;
            width: 78%;
        }

        .cv-side-box {
            background: #ffffff;
            border: 1px solid #334653;
            padding: 4mm;
            margin-bottom: 6mm;
        }

        .cv-side-box p { margin: 0 0 2.2mm; }
        .cv-stack { display: grid; gap: 1.5mm; }
        .cv-stack span {
            display: flex;
            justify-content: space-between;
            gap: 2mm;
            border-bottom: 1px solid #d5dce0;
            padding-bottom: 1mm;
            font-size: 10.5px;
        }

        .cv-stack small {
            color: var(--muted);
            white-space: nowrap;
            font-size: 9.5px;
        }

        .cv-main { padding-bottom: 7mm; }

        .cv-header {
            background: var(--navy);
            color: #ffffff;
            padding: 7mm 10mm 5mm;
            text-align: center;
            border-bottom: 5px solid #c7d0d5;
        }

        .cv-header h1 {
            margin: 0 0 3mm;
            font-size: 28px;
            font-weight: 400;
            letter-spacing: 0.02em;
        }

        .cv-header p { margin: 1mm 0; color: rgba(255,255,255,0.9); }

        .cv-main-inner { padding: 6mm 7mm 0; }

        .cv-section { margin-bottom: 5mm; }

        .cv-section-title {
            margin: 0 0 3mm;
            background: #303234;
            color: #ffffff;
            padding: 2.4mm 3mm;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-left: 3px solid var(--navy-2);
        }

        .cv-objectives {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2mm;
        }

        .cv-objectives span {
            border: 1px solid #31566f;
            padding: 2.4mm;
            text-align: center;
            font-size: 10.5px;
        }

        .cv-objectives span:nth-child(even) {
            background: var(--navy);
            color: #ffffff;
        }

        .cv-profile-box {
            border: 1px solid #e0e5e8;
            background: #fbfbfb;
            padding: 3mm 4mm;
        }

        .cv-profile-box p { margin: 0 0 1.5mm; }

        .cv-study-row,
        .cv-project-row {
            display: grid;
            grid-template-columns: 22mm 1fr 38mm;
            gap: 3mm;
            padding: 1.6mm 0;
            border-bottom: 1px solid #edf0f1;
        }

        .cv-project-row {
            color: inherit;
            text-decoration: none;
        }

        .cv-project-row:hover strong,
        .cv-project-row:hover .cv-project-url {
            text-decoration: underline;
        }

        .cv-study-row strong,
        .cv-project-row strong { font-weight: 800; }

        .cv-timeline { border-left: 2px solid #2f5b78; margin-left: 24mm; }

        .cv-timeline-continuous {
            position: relative;
            min-height: 198mm;
        }

        .cv-job {
            position: relative;
            display: grid;
            grid-template-columns: 25mm 1fr;
            gap: 4mm;
            margin-bottom: 3mm;
            margin-left: -24mm;
            padding: 2mm 0 2.5mm;
            border-bottom: 1px solid #e5eaed;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .cv-job::before {
            content: "";
            position: absolute;
            left: 23.6mm;
            top: 5mm;
            width: 2.4mm;
            height: 2.4mm;
            border-radius: 50%;
            background: var(--navy-2);
        }

        .cv-job-period { color: #1f2933; font-size: 10.5px; }
        .cv-job h3 { margin: 0 0 1mm; font-size: 12px; text-transform: uppercase; }
        .cv-job-title { margin: 0 0 1.4mm; color: var(--muted); font-style: italic; }
        .cv-job ul { margin: 0; padding-left: 4mm; }
        .cv-job li { margin-bottom: 0.8mm; }

        .cv-link {
            color: inherit;
            text-decoration: none;
        }

        .cv-link:hover {
            text-decoration: underline;
        }

        .cv-footer-note {
            color: var(--muted);
            font-size: 9.5px;
            margin-top: 4mm;
            text-align: right;
        }

        @page { size: A4; margin: 0; }

        @media print {
            html,
            body {
                width: 210mm;
                min-width: 210mm;
                background: #ffffff;
            }

            body {
                margin: 0;
            }

            .cv-toolbar { display: none; }
            .cv-page {
                width: 210mm;
                height: 297mm;
                min-height: 0;
                margin: 0;
                box-shadow: none;
                overflow: hidden;
                break-after: page;
                page-break-after: always;
            }

            .cv-page:last-of-type {
                page-break-after: auto;
            }

            .cv-page + .cv-page {
                margin-top: 0;
            }

            .cv-shell {
                display: grid;
                grid-template-columns: 52mm 1fr;
                min-height: 297mm;
            }

            .cv-side {
                border-right: 2px solid #254a65;
                background: #f0f2ef;
            }

            .cv-header,
            .cv-badge,
            .cv-side-title,
            .cv-section-title,
            .cv-objectives span:nth-child(even) {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media screen and (max-width: 860px) {
            .cv-page { width: 100%; margin: 0; }
            .cv-shell { grid-template-columns: 1fr; }
            .cv-side { border-right: 0; }
            .cv-photo { height: 260px; }
            .cv-objectives { grid-template-columns: 1fr; }
            .cv-study-row,
            .cv-project-row,
            .cv-job { grid-template-columns: 1fr; }
            .cv-timeline { border-left: 0; margin-left: 0; }
            .cv-job { margin-left: 0; }
            .cv-job::before { display: none; }
        }
    </style>
</head>
<body>
<div class="cv-toolbar">
    <button type="button" onclick="window.print()"><?= cv_e($labels['print']) ?></button>
    <a href="<?= $lang === 'en' ? '/en' : '/' ?>"><?= cv_e($labels['back']) ?></a>
</div>

<article class="cv-page">
    <div class="cv-shell">
        <aside class="cv-side">
            <img class="cv-photo" src="<?= cv_e($cvPhoto) ?>" alt="<?= cv_e($profile['name']) ?>">
            <div class="cv-side-inner">
                <div class="cv-side-box">
                    <p><?= cv_e(cv_text($profile['location'], $lang)) ?></p>
                    <p><?= cv_e(cv_text($profile['age'], $lang)) ?></p>
                </div>

                <div class="cv-badge"><?= cv_e($labels['stack']) ?></div>

                <div class="cv-side-box">
                    <div class="cv-stack">
                        <?php foreach ($skillDetails as $skill): ?>
                            <span>
                                <strong><?= cv_e((string) ($skill['name'] ?? '')) ?></strong>
                                <?php if (!empty($skill['years_label'])): ?>
                                    <small><?= cv_e((string) $skill['years_label']) ?></small>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="cv-badge"><?= cv_e($labels['contact']) ?></div>
                <div class="cv-side-box">
                    <p><strong><?= cv_e($labels['languages']) ?></strong><br>Català C1<br>Castellano C1<br><?= cv_e($labels['english_b2']) ?></p>
                    <p><strong><?= cv_e($labels['driving_license']) ?></strong></p>
                    <p><?= cv_e($labels['own_vehicle']) ?></p>
                </div>
            </div>
        </aside>

        <main class="cv-main">
            <header class="cv-header">
                <h1><?= cv_e($profile['name']) ?></h1>
                <p><a class="cv-link" href="mailto:<?= cv_e($profile['email']) ?>"><?= cv_e($profile['email']) ?></a></p>
                <p><a class="cv-link" href="<?= cv_e($profile['linkedin']) ?>" target="_blank" rel="noopener noreferrer"><?= cv_e(cv_clean_url_label($profile['linkedin'])) ?></a></p>
                <p><a class="cv-link" href="https://lawebdejacoto.com/" target="_blank" rel="noopener noreferrer"><?= cv_e($labels['portfolio']) ?></a></p>
                <p><a class="cv-link" href="<?= cv_e(cv_phone_href($profile['phone'])) ?>"><?= cv_e($profile['phone']) ?></a></p>
            </header>

            <div class="cv-main-inner">
                <section class="cv-section">
                    <h2 class="cv-section-title"><?= cv_e($labels['objective']) ?></h2>
                    <div class="cv-objectives">
                        <?php foreach (array_slice($objectiveItems, 0, 3) as $item): ?>
                            <span>/ <?= cv_e($item) ?></span>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="cv-section">
                    <h2 class="cv-section-title"><?= cv_e($labels['profile']) ?></h2>
                    <div class="cv-profile-box">
                        <p><?= cv_e(cv_text($profile['bio'], $lang)) ?></p>
                        <?php foreach (array_slice(($profile['services'] ?? []), 0, 3) as $service): ?>
                            <p>- <?= cv_e(cv_text($service['desc'], $lang)) ?></p>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="cv-section">
                    <h2 class="cv-section-title"><?= cv_e($labels['education']) ?></h2>
                    <?php foreach (($profile['studies'] ?? []) as $study): ?>
                        <div class="cv-study-row">
                            <span><?= cv_e(cv_text($study['period'], $lang)) ?></span>
                            <strong><?= cv_e(cv_text($study['title'], $lang)) ?></strong>
                            <span><?= cv_e(cv_text($study['center'], $lang)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </section>

                <section class="cv-section">
                    <h2 class="cv-section-title"><?= cv_e($labels['experience']) ?></h2>
                    <div class="cv-timeline">
                        <?php foreach ($firstPageJobs as $job): ?>
                            <article class="cv-job">
                                <?php $duration = app_experience_duration($job['start_date'] ?? '', $job['end_date'] ?? '', $lang); ?>
                                <div class="cv-job-period">
                                    <?= cv_e(cv_text($job['period'], $lang)) ?>
                                    <?php if ($duration !== ''): ?><br><strong><?= cv_e($duration) ?></strong><?php endif; ?>
                                </div>
                                <div>
                                    <h3><?= cv_e($job['company']) ?></h3>
                                    <p class="cv-job-title"><?= cv_e(cv_text($job['title'], $lang)) ?></p>
                                    <ul>
                                        <li><?= cv_e(cv_first_words(cv_text($job['summary'], $lang), 26)) ?></li>
                                        <?php foreach (array_slice(($job['projects'] ?? []), 0, 2) as $project): ?>
                                            <li><?= cv_e(cv_first_words((string) ($project[$lang] ?? $project['es'] ?? ''), 24)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <p class="cv-footer-note"><?= cv_e($labels['generated']) ?></p>
            </div>
        </main>
    </div>
</article>

<?php if ($remainingJobs !== []): ?>
    <article class="cv-page cv-page-continuation">
        <div class="cv-main-inner">
            <section class="cv-section">
                <h2 class="cv-section-title"><?= cv_e($labels['experience']) ?> · <?= cv_e($labels['continued']) ?></h2>
                <div class="cv-timeline cv-timeline-continuous">
                    <?php foreach ($remainingJobs as $job): ?>
                        <article class="cv-job">
                            <?php $duration = app_experience_duration($job['start_date'] ?? '', $job['end_date'] ?? '', $lang); ?>
                            <div class="cv-job-period">
                                <?= cv_e(cv_text($job['period'], $lang)) ?>
                                <?php if ($duration !== ''): ?><br><strong><?= cv_e($duration) ?></strong><?php endif; ?>
                            </div>
                            <div>
                                <h3><?= cv_e($job['company']) ?></h3>
                                <p class="cv-job-title"><?= cv_e(cv_text($job['title'], $lang)) ?></p>
                                <ul>
                                    <li><?= cv_e(cv_first_words(cv_text($job['summary'], $lang), 24)) ?></li>
                                    <?php foreach (array_slice(($job['projects'] ?? []), 0, 1) as $project): ?>
                                        <li><?= cv_e(cv_first_words((string) ($project[$lang] ?? $project['es'] ?? ''), 22)) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="cv-section">
                <h2 class="cv-section-title"><?= cv_e($labels['projects']) ?></h2>
                <?php foreach (array_slice(($profile['projects'] ?? []), 0, 5) as $project): ?>
                    <?php $projectUrl = cv_project_url((string) ($project['url'] ?? ''), $lang); ?>
                    <a class="cv-project-row" href="<?= cv_e($projectUrl) ?>" target="_blank" rel="noopener noreferrer">
                        <span><?= cv_e(cv_text($project['type'], $lang)) ?></span>
                        <strong><?= cv_e($project['name']) ?></strong>
                        <span class="cv-project-url"><?= cv_e(cv_clean_url_label($projectUrl)) ?></span>
                    </a>
                <?php endforeach; ?>
            </section>
        </div>
    </article>
<?php endif; ?>

<?php if ($print): ?>
    <script>
        window.addEventListener('load', () => {
            window.setTimeout(() => window.print(), 350);
        });
    </script>
<?php endif; ?>
</body>
</html>
