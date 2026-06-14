<?php

declare(strict_types=1);

function app_uid(): string
{
    return bin2hex(random_bytes(8));
}

function app_db_driver(PDO $pdo): string
{
    return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
}

function app_run_portfolio_migrations(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_meta (meta_key VARCHAR(80) PRIMARY KEY, meta_value TEXT NOT NULL)');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_profile (id VARCHAR(16) PRIMARY KEY, name VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(80) NOT NULL, linkedin VARCHAR(255) NOT NULL, cv VARCHAR(255) NOT NULL, cv_download_name VARCHAR(255) NOT NULL, photo VARCHAR(255) NOT NULL, cv_photo VARCHAR(255) NOT NULL DEFAULT \'\', golf_photo VARCHAR(255) NOT NULL, updated_at VARCHAR(32) NOT NULL)');
    app_ensure_column($pdo, 'portfolio_profile', 'cv_photo', "VARCHAR(255) NOT NULL DEFAULT ''");
    app_backfill_profile_cv_photo($pdo);
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_profile_i18n (profile_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, role TEXT NOT NULL, location TEXT NOT NULL, age TEXT NOT NULL, bio TEXT NOT NULL, hobby_title TEXT NOT NULL, hobby_text TEXT NOT NULL, PRIMARY KEY (profile_id, lang))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_texts (text_key VARCHAR(80) NOT NULL, lang VARCHAR(5) NOT NULL, text_value TEXT NOT NULL, PRIMARY KEY (text_key, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_skills (id VARCHAR(16) PRIMARY KEY, name VARCHAR(120) NOT NULL, years_label VARCHAR(80) NOT NULL DEFAULT \'\', sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    app_ensure_column($pdo, 'portfolio_skills', 'years_label', "VARCHAR(80) NOT NULL DEFAULT ''");
    app_backfill_skill_years($pdo);
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_highlights (id VARCHAR(16) PRIMARY KEY, metric VARCHAR(40) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_highlight_i18n (highlight_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, label TEXT NOT NULL, PRIMARY KEY (highlight_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_studies (id VARCHAR(16) PRIMARY KEY, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_study_i18n (study_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, period TEXT NOT NULL, title TEXT NOT NULL, center TEXT NOT NULL, PRIMARY KEY (study_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experiences (id VARCHAR(16) PRIMARY KEY, company VARCHAR(180) NOT NULL, start_date VARCHAR(10) NOT NULL DEFAULT \'\', end_date VARCHAR(10) NOT NULL DEFAULT \'\', sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    app_ensure_column($pdo, 'portfolio_experiences', 'start_date', "VARCHAR(10) NOT NULL DEFAULT ''");
    app_ensure_column($pdo, 'portfolio_experiences', 'end_date', "VARCHAR(10) NOT NULL DEFAULT ''");
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_i18n (experience_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, period TEXT NOT NULL, title TEXT NOT NULL, summary TEXT NOT NULL, PRIMARY KEY (experience_id, lang))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_tags (id VARCHAR(16) PRIMARY KEY, experience_id VARCHAR(16) NOT NULL, name VARCHAR(80) NOT NULL, sort_order INT NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_projects (id VARCHAR(16) PRIMARY KEY, experience_id VARCHAR(16) NOT NULL, url VARCHAR(255) NOT NULL DEFAULT \'\', sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_project_i18n (project_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, text_value TEXT NOT NULL, url_label TEXT NOT NULL, PRIMARY KEY (project_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_services (id VARCHAR(16) PRIMARY KEY, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_service_i18n (service_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, title TEXT NOT NULL, description TEXT NOT NULL, PRIMARY KEY (service_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_projects (id VARCHAR(16) PRIMARY KEY, name VARCHAR(180) NOT NULL, logo VARCHAR(40) NOT NULL, url VARCHAR(255) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_project_i18n (project_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, type TEXT NOT NULL, description TEXT NOT NULL, PRIMARY KEY (project_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_hero_cards (id VARCHAR(16) PRIMARY KEY, name VARCHAR(80) NOT NULL, label VARCHAR(120) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_hero_card_items (id VARCHAR(16) PRIMARY KEY, card_id VARCHAR(16) NOT NULL, label VARCHAR(120) NOT NULL, url VARCHAR(255) NOT NULL DEFAULT \'\', sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    app_backfill_hero_cards($pdo);
    app_backfill_hero_typewriter($pdo);
}

function app_column_exists(PDO $pdo, string $table, string $column): bool
{
    if (app_db_driver($pdo) === 'sqlite') {
        $stmt = $pdo->query('PRAGMA table_info(' . $table . ')');
        foreach ($stmt->fetchAll() as $row) {
            if (($row['name'] ?? '') === $column) {
                return true;
            }
        }
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
    $stmt->execute(['table_name' => $table, 'column_name' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function app_ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!app_column_exists($pdo, $table, $column)) {
        $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
    }
}

function app_default_skill_years(): array
{
    return [
        '.NET / C#' => '3 años',
        'Laravel / PHP' => '3 años',
        'Angular' => '2-3 años',
        'Python / Odoo' => '2 años',
        'PostgreSQL / SQL' => '3 años',
        'Shopify Liquid' => '1 año',
        'SAP' => '1 año',
        'GitHub' => '2 años',
    ];
}

function app_backfill_profile_cv_photo(PDO $pdo): void
{
    $stmt = $pdo->prepare("UPDATE portfolio_profile SET cv_photo = :cv_photo WHERE id = 'main' AND (cv_photo IS NULL OR cv_photo = '')");
    $stmt->execute(['cv_photo' => 'images/perfil_joseangel_linkedin.jpg']);
}

function app_backfill_hero_cards(PDO $pdo): void
{
    $stmt = $pdo->query("SELECT COUNT(*) FROM portfolio_hero_cards");
    if ((int) $stmt->fetchColumn() > 0) return;

    $cards = [
        ['name' => 'fullstack', 'label' => 'Full Stack', 'items' => 'Laravel / PHP::#experience|.NET / C#::#experience|Angular::#experience|SQL / APIs::#services'],
        ['name' => 'erp', 'label' => 'ERP', 'items' => 'Odoo::#experience|SAP::#experience|Automatizaciones::#services|Procesos internos::#services'],
        ['name' => 'ecommerce', 'label' => 'E-commerce', 'items' => 'Shopify::#experience|SticNow::https://sticnow.com|Checkout::#services|Conversión::#projects'],
        ['name' => 'logistica', 'label' => 'Logística', 'items' => 'Paso Seguro::https://pasoseguro.pro|Proyecto Logístico::/proyecto-logistica/index.php?lang=es|Trazabilidad::#projects|Dashboards::#services'],
        ['name' => 'producto', 'label' => 'Producto', 'items' => 'Jacoto Fotografía::https://jacotofotografia.com|Landings::#services|SEO técnico::#projects|Analítica::#contact'],
    ];

    foreach ($cards as $index => $card) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_hero_cards', ['id'], ['id' => $id, 'name' => $card['name'], 'label' => $card['label'], 'sort_order' => $index, 'is_active' => 1]);
        $parts = array_values(array_filter(array_map('trim', explode('|', $card['items']))));
        foreach ($parts as $itemIndex => $part) {
            [$itemLabel, $itemUrl] = array_pad(array_map('trim', explode('::', $part)), 2, '#projects');
            app_upsert($pdo, 'portfolio_hero_card_items', ['id'], ['id' => app_uid(), 'card_id' => $id, 'label' => $itemLabel, 'url' => $itemUrl, 'sort_order' => $itemIndex, 'is_active' => 1]);
        }
    }
}

function app_backfill_hero_typewriter(PDO $pdo): void
{
    foreach (['es', 'en'] as $lang) {
        $text = $lang === 'es'
            ? 'Hola, soy José Angel. Desarrollo soluciones, aplicaciones top y webs que venden.'
            : 'Hi, I\'m José Angel. I build solutions, top apps, and websites that sell.';
        app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => 'hero_typewriter', 'lang' => $lang, 'text_value' => $text]);
    }
}

function app_experience_duration(?string $startDate, ?string $endDate, string $lang = 'es'): string
{
    $startDate = trim((string) $startDate);
    $endDate = trim((string) $endDate);

    if ($startDate === '') {
        return '';
    }

    try {
        $start = new DateTimeImmutable($startDate);
        $end = $endDate !== '' ? new DateTimeImmutable($endDate) : new DateTimeImmutable('today');
    } catch (Throwable $exception) {
        return '';
    }

    if ($end < $start) {
        return '';
    }

    $diff = $start->diff($end);
    $months = ($diff->y * 12) + $diff->m;
    if ($diff->d > 0 || $months === 0) {
        $months += 1;
    }

    $years = intdiv($months, 12);
    $remainingMonths = $months % 12;

    if ($lang === 'en') {
        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' ' . ($years === 1 ? 'year' : 'years');
        }
        if ($remainingMonths > 0) {
            $parts[] = $remainingMonths . ' ' . ($remainingMonths === 1 ? 'month' : 'months');
        }
        return implode(' and ', $parts);
    }

    $parts = [];
    if ($years > 0) {
        $parts[] = $years . ' ' . ($years === 1 ? 'año' : 'años');
    }
    if ($remainingMonths > 0) {
        $parts[] = $remainingMonths . ' ' . ($remainingMonths === 1 ? 'mes' : 'meses');
    }

    return implode(' y ', $parts);
}

function app_ensure_paso_seguro_project(PDO $pdo): void
{
    $stmt = $pdo->prepare('SELECT id FROM portfolio_projects WHERE name = :name LIMIT 1');
    $stmt->execute(['name' => 'Paso Seguro']);
    $existingId = $stmt->fetchColumn();
    if ($existingId !== false) {
        return;
    }

    $projectId = 'pasoseguro';

    app_upsert($pdo, 'portfolio_projects', ['id'], [
        'id' => $projectId,
        'name' => 'Paso Seguro',
        'logo' => 'PS',
        'url' => 'https://pasoseguro.pro',
        'sort_order' => 99,
        'is_active' => 1,
    ]);
    app_upsert($pdo, 'portfolio_project_i18n', ['project_id', 'lang'], [
        'project_id' => $projectId,
        'lang' => 'es',
        'type' => 'Proyecto de seguridad operativa',
        'description' => 'Proyecto orientado a control preventivo, trazabilidad y comunicación clara de estados para reforzar decisiones seguras en operación.',
    ]);
    app_upsert($pdo, 'portfolio_project_i18n', ['project_id', 'lang'], [
        'project_id' => $projectId,
        'lang' => 'en',
        'type' => 'Operational safety project',
        'description' => 'Project focused on preventive control, traceability, and clear status communication to support safer operational decisions.',
    ]);
}

function app_backfill_skill_years(PDO $pdo): void
{
    $stmt = $pdo->prepare('UPDATE portfolio_skills SET years_label = :years_label WHERE name = :name AND (years_label IS NULL OR years_label = \'\')');
    foreach (app_default_skill_years() as $name => $yearsLabel) {
        $stmt->execute(['years_label' => $yearsLabel, 'name' => $name]);
    }
}

function app_portfolio_is_seeded(PDO $pdo): bool
{
    $stmt = $pdo->prepare('SELECT meta_value FROM portfolio_meta WHERE meta_key = :key LIMIT 1');
    $stmt->execute(['key' => 'content_seeded_at']);
    return $stmt->fetchColumn() !== false;
}

function app_upsert(PDO $pdo, string $table, array $keys, array $data): void
{
    $where = [];
    foreach ($keys as $key) {
        $where[] = $key . ' = :' . $key;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . implode(' AND ', $where));
    $stmt->execute(array_intersect_key($data, array_flip($keys)));
    $exists = (int) $stmt->fetchColumn() > 0;

    if ($exists) {
        $sets = [];
        foreach ($data as $field => $value) {
            if (!in_array($field, $keys, true)) {
                $sets[] = $field . ' = :' . $field;
            }
        }
        if ($sets === []) {
            return;
        }
        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . implode(' AND ', $where);
    } else {
        $fields = array_keys($data);
        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (:' . implode(', :', $fields) . ')';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
}

function app_seed_portfolio_content(PDO $pdo): void
{
    if (app_portfolio_is_seeded($pdo)) {
        app_ensure_paso_seguro_project($pdo);
        return;
    }

    $profile = require APP_BASE_PATH . '/data/profile.php';
    $translations = require APP_BASE_PATH . '/data/translations.php';

    app_upsert($pdo, 'portfolio_profile', ['id'], [
        'id' => 'main',
        'name' => $profile['name'],
        'email' => $profile['email'],
        'phone' => $profile['phone'],
        'linkedin' => $profile['linkedin'],
        'cv' => $profile['cv'],
        'cv_download_name' => $profile['cv_download_name'],
        'photo' => $profile['photo'],
        'cv_photo' => $profile['cv_photo'] ?? 'images/perfil_joseangel_linkedin.jpg',
        'golf_photo' => $profile['golf_photo'],
        'updated_at' => app_now(),
    ]);

    foreach (['es', 'en'] as $lang) {
        app_upsert($pdo, 'portfolio_profile_i18n', ['profile_id', 'lang'], [
            'profile_id' => 'main',
            'lang' => $lang,
            'role' => $profile['role'][$lang] ?? $profile['role']['es'],
            'location' => $profile['location'][$lang] ?? $profile['location']['es'],
            'age' => $profile['age'][$lang] ?? $profile['age']['es'],
            'bio' => $profile['bio'][$lang] ?? $profile['bio']['es'],
            'hobby_title' => $profile['hobby']['title'][$lang] ?? $profile['hobby']['title']['es'],
            'hobby_text' => $profile['hobby']['text'][$lang] ?? $profile['hobby']['text']['es'],
        ]);
    }

    foreach ($translations as $lang => $items) {
        foreach ($items as $key => $value) {
            app_upsert($pdo, 'portfolio_texts', ['text_key', 'lang'], ['text_key' => $key, 'lang' => $lang, 'text_value' => $value]);
        }
    }

    foreach ($profile['skills'] as $index => $skill) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_skills', ['id'], ['id' => $id, 'name' => $skill, 'sort_order' => $index, 'is_active' => 1]);
    }

    foreach ($profile['highlights'] as $index => $highlight) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_highlights', ['id'], ['id' => $id, 'metric' => $highlight['metric'], 'sort_order' => $index, 'is_active' => 1]);
        foreach (['es', 'en'] as $lang) {
            app_upsert($pdo, 'portfolio_highlight_i18n', ['highlight_id', 'lang'], ['highlight_id' => $id, 'lang' => $lang, 'label' => $highlight['label'][$lang] ?? $highlight['label']['es']]);
        }
    }

    foreach ($profile['studies'] as $index => $study) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_studies', ['id'], ['id' => $id, 'sort_order' => $index, 'is_active' => 1]);
        foreach (['es', 'en'] as $lang) {
            app_upsert($pdo, 'portfolio_study_i18n', ['study_id', 'lang'], [
                'study_id' => $id,
                'lang' => $lang,
                'period' => $study['period'][$lang] ?? $study['period']['es'],
                'title' => $study['title'][$lang] ?? $study['title']['es'],
                'center' => $study['center'][$lang] ?? $study['center']['es'],
            ]);
        }
    }

    foreach ($profile['experience'] as $index => $job) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_experiences', ['id'], ['id' => $id, 'company' => $job['company'], 'start_date' => '', 'end_date' => '', 'sort_order' => $index, 'is_active' => 1]);
        foreach (['es', 'en'] as $lang) {
            app_upsert($pdo, 'portfolio_experience_i18n', ['experience_id', 'lang'], [
                'experience_id' => $id,
                'lang' => $lang,
                'period' => $job['period'][$lang] ?? $job['period']['es'],
                'title' => $job['title'][$lang] ?? $job['title']['es'],
                'summary' => $job['summary'][$lang] ?? $job['summary']['es'],
            ]);
        }
        foreach (($job['tags'] ?? []) as $tagIndex => $tag) {
            app_upsert($pdo, 'portfolio_experience_tags', ['id'], ['id' => app_uid(), 'experience_id' => $id, 'name' => $tag, 'sort_order' => $tagIndex]);
        }
        foreach (($job['projects'] ?? []) as $projectIndex => $project) {
            $projectId = app_uid();
            app_upsert($pdo, 'portfolio_experience_projects', ['id'], ['id' => $projectId, 'experience_id' => $id, 'url' => $project['url'] ?? '', 'sort_order' => $projectIndex, 'is_active' => 1]);
            foreach (['es', 'en'] as $lang) {
                app_upsert($pdo, 'portfolio_experience_project_i18n', ['project_id', 'lang'], [
                    'project_id' => $projectId,
                    'lang' => $lang,
                    'text_value' => $project[$lang] ?? $project['es'],
                    'url_label' => $project['url_label'][$lang] ?? $project['url_label']['es'] ?? '',
                ]);
            }
        }
    }

    foreach ($profile['services'] as $index => $service) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_services', ['id'], ['id' => $id, 'sort_order' => $index, 'is_active' => 1]);
        foreach (['es', 'en'] as $lang) {
            app_upsert($pdo, 'portfolio_service_i18n', ['service_id', 'lang'], ['service_id' => $id, 'lang' => $lang, 'title' => $service['title'][$lang] ?? $service['title']['es'], 'description' => $service['desc'][$lang] ?? $service['desc']['es']]);
        }
    }

    foreach ($profile['projects'] as $index => $project) {
        $id = app_uid();
        app_upsert($pdo, 'portfolio_projects', ['id'], ['id' => $id, 'name' => $project['name'], 'logo' => $project['logo'] ?? 'PR', 'url' => $project['url'], 'sort_order' => $index, 'is_active' => 1]);
        foreach (['es', 'en'] as $lang) {
            app_upsert($pdo, 'portfolio_project_i18n', ['project_id', 'lang'], ['project_id' => $id, 'lang' => $lang, 'type' => $project['type'][$lang] ?? $project['type']['es'], 'description' => $project['description'][$lang] ?? $project['description']['es']]);
        }
    }

    app_upsert($pdo, 'portfolio_meta', ['meta_key'], ['meta_key' => 'content_seeded_at', 'meta_value' => app_now()]);
}

function app_rows_by_lang(PDO $pdo, string $sql, array $params, string $idField): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[$row[$idField]][$row['lang']] = $row;
    }
    return $rows;
}

function app_portfolio_profile_from_db(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM portfolio_profile WHERE id = 'main' LIMIT 1");
    $base = $stmt->fetch();
    if (!$base) {
        throw new RuntimeException('Portfolio profile is empty.');
    }

    $profile = [
        'name' => $base['name'],
        'role' => [],
        'location' => [],
        'age' => [],
        'bio' => [],
        'email' => $base['email'],
        'phone' => $base['phone'],
        'linkedin' => $base['linkedin'],
        'cv' => $base['cv'],
        'cv_download_name' => $base['cv_download_name'],
        'photo' => $base['photo'],
        'cv_photo' => $base['cv_photo'] ?: 'images/perfil_joseangel_linkedin.jpg',
        'golf_photo' => $base['golf_photo'],
        'skills' => [],
        'skill_details' => [],
        'studies' => [],
        'highlights' => [],
        'experience' => [],
        'projects' => [],
        'services' => [],
        'hobby' => ['title' => [], 'text' => []],
    ];

    $stmt = $pdo->query("SELECT * FROM portfolio_profile_i18n WHERE profile_id = 'main'");
    foreach ($stmt->fetchAll() as $row) {
        $lang = $row['lang'];
        $profile['role'][$lang] = $row['role'];
        $profile['location'][$lang] = $row['location'];
        $profile['age'][$lang] = $row['age'];
        $profile['bio'][$lang] = $row['bio'];
        $profile['hobby']['title'][$lang] = $row['hobby_title'];
        $profile['hobby']['text'][$lang] = $row['hobby_text'];
    }

    $stmt = $pdo->query('SELECT name, years_label FROM portfolio_skills WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
    foreach ($stmt->fetchAll() as $row) {
        $profile['skills'][] = $row['name'];
        $profile['skill_details'][] = ['name' => $row['name'], 'years_label' => $row['years_label'] ?? ''];
    }

    $i18n = app_rows_by_lang($pdo, 'SELECT * FROM portfolio_highlight_i18n', [], 'highlight_id');
    $stmt = $pdo->query('SELECT * FROM portfolio_highlights WHERE is_active = 1 ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $profile['highlights'][] = ['metric' => $row['metric'], 'label' => ['es' => $i18n[$row['id']]['es']['label'] ?? '', 'en' => $i18n[$row['id']]['en']['label'] ?? '']];
    }

    $i18n = app_rows_by_lang($pdo, 'SELECT * FROM portfolio_study_i18n', [], 'study_id');
    $stmt = $pdo->query('SELECT * FROM portfolio_studies WHERE is_active = 1 ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $profile['studies'][] = [
            'period' => ['es' => $i18n[$row['id']]['es']['period'] ?? '', 'en' => $i18n[$row['id']]['en']['period'] ?? ''],
            'title' => ['es' => $i18n[$row['id']]['es']['title'] ?? '', 'en' => $i18n[$row['id']]['en']['title'] ?? ''],
            'center' => ['es' => $i18n[$row['id']]['es']['center'] ?? '', 'en' => $i18n[$row['id']]['en']['center'] ?? ''],
        ];
    }

    $experienceI18n = app_rows_by_lang($pdo, 'SELECT * FROM portfolio_experience_i18n', [], 'experience_id');
    $projectI18n = app_rows_by_lang($pdo, 'SELECT * FROM portfolio_experience_project_i18n', [], 'project_id');
    $stmt = $pdo->query('SELECT * FROM portfolio_experiences WHERE is_active = 1 ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $job = [
            'period' => ['es' => $experienceI18n[$row['id']]['es']['period'] ?? '', 'en' => $experienceI18n[$row['id']]['en']['period'] ?? ''],
            'start_date' => $row['start_date'] ?? '',
            'end_date' => $row['end_date'] ?? '',
            'company' => $row['company'],
            'title' => ['es' => $experienceI18n[$row['id']]['es']['title'] ?? '', 'en' => $experienceI18n[$row['id']]['en']['title'] ?? ''],
            'summary' => ['es' => $experienceI18n[$row['id']]['es']['summary'] ?? '', 'en' => $experienceI18n[$row['id']]['en']['summary'] ?? ''],
            'tags' => [],
            'projects' => [],
        ];

        $tagStmt = $pdo->prepare('SELECT name FROM portfolio_experience_tags WHERE experience_id = :id ORDER BY sort_order ASC');
        $tagStmt->execute(['id' => $row['id']]);
        $job['tags'] = array_column($tagStmt->fetchAll(), 'name');

        $projectStmt = $pdo->prepare('SELECT * FROM portfolio_experience_projects WHERE experience_id = :id AND is_active = 1 ORDER BY sort_order ASC');
        $projectStmt->execute(['id' => $row['id']]);
        foreach ($projectStmt->fetchAll() as $project) {
            $entry = [
                'es' => $projectI18n[$project['id']]['es']['text_value'] ?? '',
                'en' => $projectI18n[$project['id']]['en']['text_value'] ?? '',
            ];
            if ($project['url'] !== '') {
                $entry['url'] = $project['url'];
                $labelEs = $projectI18n[$project['id']]['es']['url_label'] ?? '';
                $labelEn = $projectI18n[$project['id']]['en']['url_label'] ?? '';
                if ($labelEs !== '' || $labelEn !== '') {
                    $entry['url_label'] = ['es' => $labelEs, 'en' => $labelEn];
                }
            }
            $job['projects'][] = $entry;
        }

        $profile['experience'][] = $job;
    }

    $i18n = app_rows_by_lang($pdo, 'SELECT * FROM portfolio_service_i18n', [], 'service_id');
    $stmt = $pdo->query('SELECT * FROM portfolio_services WHERE is_active = 1 ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $profile['services'][] = ['title' => ['es' => $i18n[$row['id']]['es']['title'] ?? '', 'en' => $i18n[$row['id']]['en']['title'] ?? ''], 'desc' => ['es' => $i18n[$row['id']]['es']['description'] ?? '', 'en' => $i18n[$row['id']]['en']['description'] ?? '']];
    }

    $i18n = app_rows_by_lang($pdo, 'SELECT * FROM portfolio_project_i18n', [], 'project_id');
    $stmt = $pdo->query('SELECT * FROM portfolio_projects WHERE is_active = 1 ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $profile['projects'][] = ['name' => $row['name'], 'type' => ['es' => $i18n[$row['id']]['es']['type'] ?? '', 'en' => $i18n[$row['id']]['en']['type'] ?? ''], 'logo' => $row['logo'], 'description' => ['es' => $i18n[$row['id']]['es']['description'] ?? '', 'en' => $i18n[$row['id']]['en']['description'] ?? ''], 'url' => $row['url']];
    }

    return $profile;
}

function app_portfolio_hero_cards(PDO $pdo): array
{
    $cards = [];
    $stmt = $pdo->query('SELECT * FROM portfolio_hero_cards WHERE is_active = 1 ORDER BY sort_order ASC');
    foreach ($stmt->fetchAll() as $row) {
        $items = [];
        $itemStmt = $pdo->prepare('SELECT * FROM portfolio_hero_card_items WHERE card_id = :id AND is_active = 1 ORDER BY sort_order ASC');
        $itemStmt->execute(['id' => $row['id']]);
        foreach ($itemStmt->fetchAll() as $item) {
            $items[] = ['label' => $item['label'], 'url' => $item['url']];
        }
        $cards[] = ['name' => $row['name'], 'label' => $row['label'], 'items' => $items];
    }
    return $cards;
}

function app_portfolio_translations_from_db(PDO $pdo, array $fallback): array
{
    $translations = $fallback;
    $stmt = $pdo->query('SELECT * FROM portfolio_texts');
    foreach ($stmt->fetchAll() as $row) {
        $translations[$row['lang']][$row['text_key']] = $row['text_value'];
    }
    return $translations;
}

function app_public_profile(string $fallbackPath): array
{
    $fallback = require $fallbackPath;
    try {
        if (!app_database_is_configured()) {
            return $fallback;
        }
        $pdo = app_db();
        app_run_portfolio_migrations($pdo);
        return app_portfolio_profile_from_db($pdo);
    } catch (Throwable $exception) {
        return $fallback;
    }
}

function app_public_translations(string $fallbackPath): array
{
    $fallback = require $fallbackPath;
    try {
        if (!app_database_is_configured()) {
            return $fallback;
        }
        $pdo = app_db();
        return app_portfolio_translations_from_db($pdo, $fallback);
    } catch (Throwable $exception) {
        return $fallback;
    }
}
