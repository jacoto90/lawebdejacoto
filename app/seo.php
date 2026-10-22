<?php

declare(strict_types=1);
require_once __DIR__ . '/images.php';

function app_seo_url(string $path): string
{
    return 'https://lawebdejacoto.com' . $path;
}

function app_seo_page(string $key, string $lang, array $profile = []): array
{
    $copy = (require __DIR__ . '/../data/seo.php')[$lang];
    [$title, $description] = $copy[$key];
    $paths = match ($key) {
        'logistics' => ['es' => '/proyecto-logistica/', 'en' => '/proyecto-logistica/?lang=en'],
        'paso-seguro' => ['es' => '/proyecto-paso-seguro/', 'en' => '/proyecto-paso-seguro/?lang=en'],
        'cv' => ['es' => '/cv.php', 'en' => '/cv.php?lang=en'],
        // Menu routes scroll to sections of the same complete document.
        default => ['es' => '/', 'en' => '/en'],
    };
    $canonical = app_seo_url($paths[$lang]);
    $image = match ($key) {
        'logistics' => '/assets/images/proyecto-logistica-meibit.webp',
        'paso-seguro' => '/assets/images/paso-seguro-aviso-peatonal.webp',
        default => '/assets/images/jacoto-desarrollo-web-girona-' . $lang . '.png',
    };
    $imageAlt = match ($key) {
        'logistics' => $copy['projects_alt']['MB'],
        'paso-seguro' => $copy['projects_alt']['PS'],
        default => $copy['social_alt'],
    };
    $personId = app_seo_url('/#person');
    $siteId = app_seo_url('/#website');
    $graph = [[
        '@type' => 'WebSite', '@id' => $siteId,
        'url' => app_seo_url('/'), 'name' => 'Jacoto', 'alternateName' => 'La web de Jacoto',
        'inLanguage' => ['es', 'en'], 'publisher' => ['@id' => $personId],
    ]];
    $page = [
        '@type' => $key === 'cv' ? 'ProfilePage' : 'WebPage', '@id' => $canonical . '#webpage',
        'url' => $canonical, 'name' => $title, 'description' => $description,
        'inLanguage' => $lang, 'isPartOf' => ['@id' => $siteId],
        'primaryImageOfPage' => ['@id' => $canonical . '#primaryimage'],
    ];
    if ($profile !== []) {
        $portrait = $profile['cv_photo'] ?? 'images/perfil_joseangel_linkedin.jpg';
        if ($portrait === 'images/perfil_joseangel_linkedin.jpg') $portrait = app_image_path('assets/images/portrait.webp');
        $person = [
            '@type' => 'Person', '@id' => $personId, 'name' => $profile['name'],
            'alternateName' => 'Jacoto',
            'url' => app_seo_url('/'),
            'image' => preg_match('~^https?://~', $portrait) ? $portrait : app_seo_url('/' . ltrim($portrait, '/')),
            'jobTitle' => $profile['role'][$lang] ?? $profile['role']['es'],
            'description' => $profile['bio'][$lang] ?? $profile['bio']['es'],
            'homeLocation' => ['@type' => 'Place', 'name' => $profile['location'][$lang] ?? $profile['location']['es']],
            'knowsAbout' => array_values($profile['skills']),
        ];
        if (!empty($profile['linkedin'])) $person['sameAs'] = [$profile['linkedin']];
        if (!empty($profile['email'])) $person['email'] = $profile['email'];
        if (!empty($profile['phone'])) $person['telephone'] = $profile['phone'];
        $graph[] = $person;
        $page['mainEntity'] = ['@id' => $personId];
        // Derive services from the same records that generate the visible page.
        if ($key !== 'cv') foreach ($profile['services'] as $index => $service) {
            $graph[] = [
                '@type' => 'Service', '@id' => $canonical . '#service-' . ($index + 1),
                'name' => $service['title'][$lang] ?? $service['title']['es'],
                'description' => $service['desc'][$lang] ?? $service['desc']['es'],
                'provider' => ['@id' => $personId], 'url' => $canonical . '#services',
                'areaServed' => ['@type' => 'Place', 'name' => $profile['location'][$lang] ?? $profile['location']['es']],
            ];
        }
    } else {
        $page['mainEntity'] = ['@id' => $canonical . '#project'];
        $graph[] = [
            '@type' => 'CreativeWork', '@id' => $canonical . '#project',
            'name' => $key === 'logistics' ? 'MEIBIT' : 'Paso Seguro',
            'description' => $description, 'url' => $canonical, 'inLanguage' => $lang,
            'author' => ['@id' => $personId], 'image' => app_seo_url($image),
        ];
    }
    if (in_array($key, ['logistics', 'paso-seguro', 'cv'], true)) {
        $page['breadcrumb'] = ['@id' => $canonical . '#breadcrumb'];
        $graph[] = [
            '@type' => 'BreadcrumbList', '@id' => $canonical . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Jacoto', 'item' => app_seo_url($lang === 'es' ? '/' : '/en')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => match ($key) {
                    'logistics' => 'MEIBIT', 'paso-seguro' => 'Paso Seguro', default => 'CV',
                }, 'item' => $canonical],
            ],
        ];
    }
    $graph[] = $page;
    $graph[] = [
        '@type' => 'ImageObject', '@id' => $canonical . '#primaryimage',
        'contentUrl' => app_seo_url($image), 'caption' => $imageAlt,
    ];
    $dimensions = in_array($key, ['logistics', 'paso-seguro'], true) ? [1280, 900] : [1200, 630];
    return [
        'title' => $title, 'description' => $description, 'canonical' => $canonical,
        'author' => $profile['name'] ?? 'Jacoto',
        'alternates' => array_map('app_seo_url', $paths), 'lang' => $lang,
        'image' => app_seo_url($image), 'image_alt' => $imageAlt, 'dimensions' => $dimensions,
        'image_type' => str_ends_with($image, '.png') ? 'image/png' : 'image/webp',
        'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        'graph' => ['@context' => 'https://schema.org', '@graph' => $graph],
    ];
}
