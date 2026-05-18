<?php
$profile = require __DIR__ . '/../data/profile.php';
$translations = require __DIR__ . '/../data/translations.php';
$lang = $_GET['lang'] ?? 'es';
if (!isset($translations[$lang])) {
    $lang = 'es';
}
$mainCssVersion = filemtime(__DIR__ . '/../assets/css/main.css');
$headlineMobileLines = null;
if ($lang === 'es') {
    $headlineMobileLines = [
        'Timeline de',
        'operaciones',
        'logísticas',
        'y control',
        'de tiempos',
        'en muelles',
    ];
}
$heroComicBubbles = $lang === 'es'
    ? ['Muelle listo', 'Sync cloud', 'KPI en vivo']
    : ['Dock ready', 'Cloud sync', 'Live KPIs'];
$assetWithVersion = static function (string $webPath): string {
    $relativePath = ltrim(str_replace('/', DIRECTORY_SEPARATOR, $webPath), DIRECTORY_SEPARATOR);
    $absolutePath = __DIR__ . '/../' . $relativePath;
    if (!is_file($absolutePath)) {
        return $webPath;
    }
    return $webPath . '?v=' . rawurlencode((string) filemtime($absolutePath));
};

$copy = [
    'es' => [
        'title' => 'Proyecto Logístico MEIBIT',
        'eyebrow' => 'Muestra de proyecto',
        'headline' => 'Timeline de operaciones logísticas y control de tiempos en muelles',
        'subheadline' => 'De tablet en muelle a dashboard en la nube, con lectura clara para usuario de operación.',
        'project_summary' => 'Este proyecto se basa en el control de tiempos desde que un camión llega a la zona, se retiene y sigue los procesos de descarga contemplando posibles alertas y toda la información operativa. Con ello reforzamos la seguridad del proceso mediante KPIs claros y accionables.',
        'nav_overview' => 'Visión',
        'nav_timeline' => 'Timeline',
        'nav_kpi' => 'KPIs',
        'nav_value' => 'Valor',
        'nav_contact' => 'Contacto',
        'kpi_label_1' => 'Eficiencia media',
        'kpi_label_2' => 'Alertas críticas',
        'kpi_label_3' => 'Tiempo medio sesión',
        'kpi_value_1' => '94%',
        'kpi_value_2' => '3',
        'kpi_value_3' => '37 min',
        'timeline_title' => 'Recorrido del usuario',
        'timeline_intro' => 'Cada fase muestra qué ve el usuario y cómo pasa la información entre muelle, nube y panel final.',
        'step_1_title' => 'Tablet en muelle + sensores',
        'step_1_text' => 'El operario registra la sesión en tablet y los sensores confirman eventos de forma automática.',
        'step_1_caption' => 'Inicio operativo en muelle.',
        'step_2_title' => 'Sincronización cloud',
        'step_2_text' => 'Los datos suben a la nube para compartir estado en tiempo real con coordinación y oficina.',
        'step_2_caption' => 'Datos sincronizados para todos.',
        'step_3_title' => 'Vista UIX completa',
        'step_3_text' => 'En la vista UIX se resume toda la operativa: estado de muelles, actividad, alertas y trazabilidad.',
        'step_3_caption' => 'Operación consolidada en dashboard.',
        'kpi_title' => 'Zona KPI interactiva',
        'kpi_intro' => 'Toca la zona superior o inferior de la imagen para abrir cada infográfico.',
        'modal_title_1' => 'Infográfico KPI 1',
        'modal_title_2' => 'Infográfico KPI 2',
        'kpi_info_title' => 'Qué representan estos gráficos',
        'kpi_info_1' => 'Tiempos de proceso por etapa para detectar donde se concentra cada demora.',
        'kpi_info_2' => 'Responsable operativo de cada sesión para entender quién la llevó a cargo.',
        'kpi_info_3' => 'Comparativa visual entre rendimiento esperado y resultado real en cada bloque.',
        'chips_title' => 'Serie completa de control',
        'chips_intro' => 'Abajo se agrupan todos los bloques que utiliza el usuario: KPIs, alertas, configuración y estadísticas.',
        'value_title' => 'Qué aporta al día a día',
        'value_intro' => 'Menos esperas, mejor decisión operativa y seguimiento simple para todo el equipo.',
        'cta_title' => 'Proyecto listo para evolucionar',
        'cta_text' => 'Puedes ampliar esta muestra con nuevos módulos o vídeo corto de operativa real.',
        'cta_back' => 'Volver al portfolio',
        'cta_mail' => 'Solicitar demo',
    ],
    'en' => [
        'title' => 'MEIBIT Logistics Project',
        'eyebrow' => 'Project showcase',
        'headline' => 'Visual logistics flow timeline.',
        'subheadline' => 'From dock tablet to cloud dashboard, with clear reading for day-to-day operations users.',
        'project_summary' => 'This project focuses on time control from truck arrival, retention, and guided unloading stages, including all relevant alerts and operational context. It strengthens process safety through clear and actionable KPI visibility.',
        'nav_overview' => 'Overview',
        'nav_timeline' => 'Timeline',
        'nav_kpi' => 'KPIs',
        'nav_value' => 'Value',
        'nav_contact' => 'Contact',
        'kpi_label_1' => 'Average efficiency',
        'kpi_label_2' => 'Critical alerts',
        'kpi_label_3' => 'Avg session time',
        'kpi_value_1' => '94%',
        'kpi_value_2' => '3',
        'kpi_value_3' => '37 min',
        'timeline_title' => 'User journey',
        'timeline_intro' => 'Each stage shows what the user sees and how information moves across dock, cloud, and dashboard.',
        'step_1_title' => 'Dock tablet + sensors',
        'step_1_text' => 'The operator starts the session from the tablet while sensors validate events automatically.',
        'step_1_caption' => 'Operational start at dock.',
        'step_2_title' => 'Cloud sync',
        'step_2_text' => 'Data is synced to the cloud so office and supervisors see real-time status.',
        'step_2_caption' => 'Shared live visibility.',
        'step_3_title' => 'Full UIX view',
        'step_3_text' => 'The UIX view summarizes operations: dock status, activity, alerts, and process traceability.',
        'step_3_caption' => 'Consolidated operation panel.',
        'kpi_title' => 'Interactive KPI area',
        'kpi_intro' => 'Tap the upper or lower area of the image to open each infographic.',
        'modal_title_1' => 'KPI infographic 1',
        'modal_title_2' => 'KPI infographic 2',
        'kpi_info_title' => 'What these charts represent',
        'kpi_info_1' => 'Process times by stage to quickly identify where delays are concentrated.',
        'kpi_info_2' => 'Operational owner per session to understand who handled each flow.',
        'kpi_info_3' => 'Visual comparison between expected performance and real outcomes.',
        'chips_title' => 'Full control stack',
        'chips_intro' => 'Below you can see all user-facing blocks: KPIs, alerts, configuration, and statistics.',
        'value_title' => 'Day-to-day impact',
        'value_intro' => 'Less waiting, better operational decisions, and simpler team-wide monitoring.',
        'cta_title' => 'Ready to grow',
        'cta_text' => 'You can extend this showcase with new modules or a short real-operation video.',
        'cta_back' => 'Back to portfolio',
        'cta_mail' => 'Request demo',
    ],
][$lang];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($copy['title']) ?> · <?= htmlspecialchars($profile['name']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($copy['headline']) ?>">
    <link rel="stylesheet" href="/assets/css/main.css?v=<?= urlencode((string) $mainCssVersion) ?>">
</head>
<body class="logistic-page">
<div id="pageLoader" class="page-loader" aria-hidden="true">
    <div class="loader-card">
        <span></span>
        <span></span>
        <span></span>
    </div>
</div>

<header class="site-header">
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="container nav-wrap">
        <a class="brand" href="/index.php?lang=<?= urlencode($lang) ?>">JACOTO</a>
        <button class="menu-btn" type="button" id="menuBtn" aria-label="menu">Menu</button>
        <nav class="main-nav" id="mainNav">
            <a href="#top"><?= htmlspecialchars($copy['nav_overview']) ?></a>
            <a href="#timeline"><?= htmlspecialchars($copy['nav_timeline']) ?></a>
            <a href="#kpis"><?= htmlspecialchars($copy['nav_kpi']) ?></a>
            <a href="#value"><?= htmlspecialchars($copy['nav_value']) ?></a>
            <div class="lang-switch">
                <a href="?lang=es" class="<?= $lang === 'es' ? 'active' : '' ?>">ES</a>
                <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </nav>
    </div>
</header>

<main class="logistic-main" id="top">
    <section class="hero logistic-hero section">
        <div class="container logistic-wrap-narrow">
            <div class="logistic-hero-top">
                <div class="logistic-hero-copy">
                    <p class="eyebrow"><?= htmlspecialchars($copy['eyebrow']) ?></p>
                    <h1 class="logistic-headline">
                        <?php if (is_array($headlineMobileLines)): ?>
                            <span class="logistic-headline-default"><?= htmlspecialchars($copy['headline']) ?></span>
                            <span class="logistic-headline-mobile" aria-hidden="true">
                                <?php foreach ($headlineMobileLines as $line): ?>
                                    <span><?= htmlspecialchars($line) ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php else: ?>
                            <?= htmlspecialchars($copy['headline']) ?>
                        <?php endif; ?>
                    </h1>
                    <p class="logistic-intro"><?= htmlspecialchars($copy['subheadline']) ?></p>
                </div>
                <div class="logistic-hero-art" aria-hidden="true">
                    <div class="hero-timeline-ornament">
                        <div class="hero-line"></div>
                        <span class="hero-dot dot-1">01</span>
                        <span class="hero-dot dot-2">02</span>
                        <span class="hero-dot dot-3">03</span>
                        <span class="hero-comic-bubble bubble-1"><?= htmlspecialchars($heroComicBubbles[0]) ?></span>
                        <span class="hero-comic-bubble bubble-2"><?= htmlspecialchars($heroComicBubbles[1]) ?></span>
                        <span class="hero-comic-bubble bubble-3"><?= htmlspecialchars($heroComicBubbles[2]) ?></span>
                    </div>
                </div>
            </div>
            <p class="project-summary-note"><?= htmlspecialchars($copy['project_summary']) ?></p>
        </div>
    </section>

    <section class="section logistic-section" id="timeline">
        <div class="container logistic-wrap-narrow">
            <h3><?= htmlspecialchars($copy['timeline_title']) ?></h3>
            <p class="logistic-lead"><?= htmlspecialchars($copy['timeline_intro']) ?></p>

            <div class="timeline-stage">
                <ol class="project-timeline-list" id="projectTimeline">
                    <li class="timeline-point is-active" data-visual-src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/imagen1.jpg')) ?>" data-visual-alt="<?= htmlspecialchars($copy['step_1_caption']) ?>">
                        <div class="timeline-dot">01</div>
                        <div class="timeline-content">
                            <h4><?= htmlspecialchars($copy['step_1_title']) ?></h4>
                            <p><?= htmlspecialchars($copy['step_1_text']) ?></p>
                            <figure class="timeline-mobile-image">
                                <img src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/imagen1.jpg')) ?>" alt="<?= htmlspecialchars($copy['step_1_caption']) ?>" loading="lazy">
                            </figure>
                        </div>
                    </li>

                    <li class="timeline-point">
                        <div class="timeline-dot">02</div>
                        <div class="timeline-content">
                            <h4><?= htmlspecialchars($copy['step_2_title']) ?></h4>
                            <p><?= htmlspecialchars($copy['step_2_text']) ?></p>
                            <div class="cloud-sync-visual" aria-hidden="true">
                                <div class="cloud-shape"></div>
                                <span class="cloud-pulse pulse-a"></span>
                                <span class="cloud-pulse pulse-b"></span>
                                <span class="cloud-pulse pulse-c"></span>
                            </div>
                        </div>
                    </li>

                    <li class="timeline-point" data-visual-src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/imagen4.jpg')) ?>" data-visual-alt="<?= htmlspecialchars($copy['step_3_caption']) ?>">
                        <div class="timeline-dot">03</div>
                        <div class="timeline-content">
                            <h4><?= htmlspecialchars($copy['step_3_title']) ?></h4>
                            <p><?= htmlspecialchars($copy['step_3_text']) ?></p>
                            <div class="uix-image-sequence" aria-label="Secuencia visual UIX">
                                <figure class="uix-shot frame-1">
                                    <img src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/imagen3.jpg')) ?>" alt="UIX vista 1" loading="lazy">
                                </figure>
                                <figure class="uix-shot frame-2">
                                    <img src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/imagen4.jpg')) ?>" alt="UIX vista 3" loading="lazy">
                                </figure>
                            </div>
                        </div>
                    </li>
                </ol>

            </div>
        </div>
    </section>

    <section class="section logistic-section" id="kpis">
        <div class="container logistic-wrap-narrow">
            <h3><?= htmlspecialchars($copy['kpi_title']) ?></h3>
            <p class="logistic-lead"><?= htmlspecialchars($copy['kpi_intro']) ?></p>
            <article class="card kpi-modal-card">
                <div class="kpi-hotspot-image">
                    <img src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/graficos1i2.jpg')) ?>" alt="Vista unificada de gráficos KPI" loading="lazy">
                    <button class="kpi-zone kpi-zone-top" type="button" data-modal-src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/infografico1.jpg')) ?>" data-modal-title="<?= htmlspecialchars($copy['modal_title_1']) ?>" aria-label="<?= htmlspecialchars($copy['modal_title_1']) ?>"></button>
                    <button class="kpi-zone kpi-zone-bottom" type="button" data-modal-src="<?= htmlspecialchars($assetWithVersion('/images/proyecto_logistica/infografico2.jpg')) ?>" data-modal-title="<?= htmlspecialchars($copy['modal_title_2']) ?>" aria-label="<?= htmlspecialchars($copy['modal_title_2']) ?>"></button>
                </div>
            </article>

            <h4 class="chips-title"><?= htmlspecialchars($copy['kpi_info_title']) ?></h4>
            <ul class="kpi-info-list">
                <li><?= htmlspecialchars($copy['kpi_info_1']) ?></li>
                <li><?= htmlspecialchars($copy['kpi_info_2']) ?></li>
                <li><?= htmlspecialchars($copy['kpi_info_3']) ?></li>
            </ul>

            <h4 class="chips-title"><?= htmlspecialchars($copy['chips_title']) ?></h4>
            <p class="logistic-lead"><?= htmlspecialchars($copy['chips_intro']) ?></p>
            <div class="kpi-chip-cloud">
                <span class="kpi-chip chip-blue">KPIs</span>
                <span class="kpi-chip chip-amber">Alertas</span>
                <span class="kpi-chip chip-slate">Configuración de usuario</span>
                <span class="kpi-chip chip-cyan">Timeline muelles</span>
                <span class="kpi-chip chip-green">Estadísticas</span>
                <span class="kpi-chip chip-violet">Vista card</span>
                <span class="kpi-chip chip-rose">Vista mapa</span>
                <span class="kpi-chip chip-indigo">Rendimiento operario</span>
            </div>
        </div>
    </section>

    <section class="section logistic-section" id="value">
        <div class="container logistic-wrap-narrow">
            <h3><?= htmlspecialchars($copy['value_title']) ?></h3>
            <p class="logistic-lead"><?= htmlspecialchars($copy['value_intro']) ?></p>
            <div class="cards three logistic-benefits">
                <article class="card">
                    <h4>Operación más fluida</h4>
                    <p>La línea temporal facilita detectar bloqueos y reducir tiempos muertos durante la jornada.</p>
                </article>
                <article class="card">
                    <h4>Lectura rápida</h4>
                    <p>Todo se entiende de un vistazo: estado, alertas, indicadores y progreso de cada muelle.</p>
                </article>
                <article class="card">
                    <h4>Control compartido</h4>
                    <p>Mismo dato para operario, coordinador y oficina, sin perder contexto operativo.</p>
                </article>
            </div>
        </div>
    </section>

</main>

<div class="media-modal" id="mediaModal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Media modal">
    <div class="media-modal-backdrop" data-modal-close></div>
    <div class="media-modal-card">
        <button type="button" class="media-modal-close" data-modal-close aria-label="Close">x</button>
        <figure>
            <img id="mediaModalImage" src="" alt="">
            <figcaption id="mediaModalCaption"></figcaption>
        </figure>
    </div>
</div>

<footer class="site-footer">
    <div class="container footer-wrap">
        <p>&copy; 2026 <?= htmlspecialchars($profile['name']) ?> · <?= htmlspecialchars($translations[$lang]['footer_copy']) ?></p>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
