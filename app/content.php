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

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_profile (id VARCHAR(16) PRIMARY KEY, name VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(80) NOT NULL, linkedin VARCHAR(255) NOT NULL, cv VARCHAR(255) NOT NULL, cv_download_name VARCHAR(255) NOT NULL, photo VARCHAR(255) NOT NULL, golf_photo VARCHAR(255) NOT NULL, updated_at VARCHAR(32) NOT NULL)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_profile_i18n (profile_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, role TEXT NOT NULL, location TEXT NOT NULL, age TEXT NOT NULL, bio TEXT NOT NULL, hobby_title TEXT NOT NULL, hobby_text TEXT NOT NULL, PRIMARY KEY (profile_id, lang))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_texts (text_key VARCHAR(80) NOT NULL, lang VARCHAR(5) NOT NULL, text_value TEXT NOT NULL, PRIMARY KEY (text_key, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_skills (id VARCHAR(16) PRIMARY KEY, name VARCHAR(120) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_highlights (id VARCHAR(16) PRIMARY KEY, metric VARCHAR(40) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_highlight_i18n (highlight_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, label TEXT NOT NULL, PRIMARY KEY (highlight_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_studies (id VARCHAR(16) PRIMARY KEY, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_study_i18n (study_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, period TEXT NOT NULL, title TEXT NOT NULL, center TEXT NOT NULL, PRIMARY KEY (study_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experiences (id VARCHAR(16) PRIMARY KEY, company VARCHAR(180) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_i18n (experience_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, period TEXT NOT NULL, title TEXT NOT NULL, summary TEXT NOT NULL, PRIMARY KEY (experience_id, lang))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_tags (id VARCHAR(16) PRIMARY KEY, experience_id VARCHAR(16) NOT NULL, name VARCHAR(80) NOT NULL, sort_order INT NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_projects (id VARCHAR(16) PRIMARY KEY, experience_id VARCHAR(16) NOT NULL, url VARCHAR(255) NOT NULL DEFAULT \'\', sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_experience_project_i18n (project_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, text_value TEXT NOT NULL, url_label TEXT NOT NULL, PRIMARY KEY (project_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_services (id VARCHAR(16) PRIMARY KEY, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_service_i18n (service_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, title TEXT NOT NULL, description TEXT NOT NULL, PRIMARY KEY (service_id, lang))');

    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_projects (id VARCHAR(16) PRIMARY KEY, name VARCHAR(180) NOT NULL, logo VARCHAR(40) NOT NULL, url VARCHAR(255) NOT NULL, sort_order INT NOT NULL DEFAULT 0, is_active INT NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS portfolio_project_i18n (project_id VARCHAR(16) NOT NULL, lang VARCHAR(5) NOT NULL, type TEXT NOT NULL, description TEXT NOT NULL, PRIMARY KEY (project_id, lang))');
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
        app_upsert($pdo, 'portfolio_experiences', ['id'], ['id' => $id, 'company' => $job['company'], 'sort_order' => $index, 'is_active' => 1]);
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
        'golf_photo' => $base['golf_photo'],
        'skills' => [],
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

    $stmt = $pdo->query('SELECT name FROM portfolio_skills WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
    $profile['skills'] = array_column($stmt->fetchAll(), 'name');

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
