<?php

declare(strict_types=1);

function studio_field(string $label, string $type = 'text', bool $required = false, int $max = 255, string $hint = ''): array
{
    return compact('label', 'type', 'required', 'max', 'hint');
}

function studio_sections(): array
{
    return [
        'overview' => ['label' => 'Vista general', 'icon' => 'grid', 'description' => 'Tu portfolio, bajo control.'],
        'profile' => ['label' => 'Perfil personal', 'icon' => 'user', 'description' => 'Tu identidad, presentación, contacto y fotografías.'],
        'experience' => ['label' => 'Experiencia', 'icon' => 'briefcase', 'description' => 'Tu recorrido profesional y los proyectos de cada etapa.'],
        'projects' => ['label' => 'Proyectos', 'icon' => 'layers', 'description' => 'Selecciona lo que muestras. Puedes ocultar un proyecto y recuperarlo después.'],
        'services' => ['label' => 'Servicios', 'icon' => 'spark', 'description' => 'Lo que haces y el valor que aportas.'],
        'skills' => ['label' => 'Tecnologías', 'icon' => 'code', 'description' => 'Tu stack y los años de experiencia que aparecen en el CV.'],
        'studies' => ['label' => 'Formación', 'icon' => 'book', 'description' => 'Estudios, centros y fechas.'],
        'highlights' => ['label' => 'Cifras destacadas', 'icon' => 'chart', 'description' => 'Los números que cuentan tu trayectoria.'],
        'hero' => ['label' => 'Especialidades', 'icon' => 'compass', 'description' => 'La frase de presentación y los enlaces de tus especialidades.'],
        'design' => ['label' => 'Textos del diseño', 'icon' => 'type', 'description' => 'Titulares, llamadas a la acción y detalles de la nueva identidad.'],
        'texts' => ['label' => 'Textos y SEO', 'icon' => 'globe', 'description' => 'Navegación, contenido general y descripción para buscadores.'],
    ];
}

function studio_schema(): array
{
    $order = studio_field('Posición', 'number', true, 6, 'Los números más bajos aparecen primero.');
    $active = studio_field('Visible en el portfolio', 'checkbox');
    return [
        'profile' => ['section' => 'profile', 'label' => 'Identidad y contacto', 'table' => 'portfolio_profile', 'singleton' => true, 'title' => 'name', 'fields' => [
            'name' => studio_field('Nombre completo', 'text', true, 180), 'email' => studio_field('Email', 'email', true, 180),
            'phone' => studio_field('Teléfono', 'tel', false, 80), 'linkedin' => studio_field('LinkedIn', 'url'),
            'cv' => studio_field('Archivo de CV', 'path', true, 255, 'Ruta del PDF, por ejemplo docs/mi-cv.pdf.'),
            'cv_download_name' => studio_field('Nombre del PDF al descargar', 'text', true),
            'photo' => studio_field('Imagen de perfil', 'path'), 'cv_photo' => studio_field('Retrato principal y CV', 'path', true, 255, 'Ruta de la imagen. El retrato original se sirve optimizado en WebP.'),
        ]],
        'intro' => ['section' => 'profile', 'label' => 'Presentación', 'table' => 'portfolio_profile', 'singleton' => true, 'fields' => [], 'i18n' => ['table' => 'portfolio_profile_i18n', 'foreign' => 'profile_id', 'fields' => [
            'role' => studio_field('Titular profesional', 'text', true, 500), 'location' => studio_field('Ubicación', 'text', true, 180),
            'age' => studio_field('Edad', 'text', false, 80), 'bio' => studio_field('Presentación breve', 'textarea', true, 4000),
        ]]],
        'hobby' => ['section' => 'profile', 'label' => 'Fuera de la pantalla', 'table' => 'portfolio_profile', 'singleton' => true, 'fields' => [
            'golf_photo' => studio_field('Fotografía de golf', 'path', true),
        ], 'i18n' => ['table' => 'portfolio_profile_i18n', 'foreign' => 'profile_id', 'fields' => [
            'hobby_title' => studio_field('Título', 'text', true, 500), 'hobby_text' => studio_field('Descripción', 'textarea', true, 4000),
        ]]],
        'skill' => ['section' => 'skills', 'label' => 'Tecnología', 'table' => 'portfolio_skills', 'title' => 'name', 'fields' => [
            'name' => studio_field('Tecnología', 'text', true, 120), 'years_label' => studio_field('Experiencia para el CV', 'text', false, 80, 'Por ejemplo: 3 años. Puedes dejarlo vacío.'), 'sort_order' => $order, 'is_active' => $active,
        ]],
        'highlight' => ['section' => 'highlights', 'label' => 'Cifra destacada', 'table' => 'portfolio_highlights', 'title' => 'metric', 'fields' => [
            'metric' => studio_field('Cifra', 'text', true, 40, 'Por ejemplo: 20+'), 'sort_order' => $order, 'is_active' => $active,
        ], 'i18n' => ['table' => 'portfolio_highlight_i18n', 'foreign' => 'highlight_id', 'fields' => ['label' => studio_field('Descripción de la cifra', 'text', true, 300)]]],
        'study' => ['section' => 'studies', 'label' => 'Formación', 'table' => 'portfolio_studies', 'title' => 'title_es', 'fields' => ['sort_order' => $order, 'is_active' => $active], 'i18n' => ['table' => 'portfolio_study_i18n', 'foreign' => 'study_id', 'fields' => [
            'title' => studio_field('Titulación', 'text', true, 500), 'center' => studio_field('Centro', 'text', true, 500), 'period' => studio_field('Periodo', 'text', true, 180),
        ]]],
        'service' => ['section' => 'services', 'label' => 'Servicio', 'table' => 'portfolio_services', 'title' => 'title_es', 'fields' => ['sort_order' => $order, 'is_active' => $active], 'i18n' => ['table' => 'portfolio_service_i18n', 'foreign' => 'service_id', 'fields' => [
            'title' => studio_field('Nombre del servicio', 'text', true, 500), 'description' => studio_field('Descripción', 'textarea', true, 6000),
        ]]],
        'project' => ['section' => 'projects', 'label' => 'Proyecto', 'table' => 'portfolio_projects', 'title' => 'name', 'fields' => [
            'name' => studio_field('Nombre', 'text', true, 180), 'url' => studio_field('Enlace al proyecto', 'url', true),
            'logo' => studio_field('Identificador visual', 'text', false, 40, 'MB, JF, SN y DS usan sus vistas previas. Otros valores se muestran como monograma.'), 'sort_order' => $order, 'is_active' => $active,
        ], 'i18n' => ['table' => 'portfolio_project_i18n', 'foreign' => 'project_id', 'fields' => [
            'type' => studio_field('Tipo de proyecto', 'text', true, 500), 'description' => studio_field('Descripción', 'textarea', true, 6000),
        ]]],
        'experience' => ['section' => 'experience', 'label' => 'Experiencia', 'table' => 'portfolio_experiences', 'title' => 'company', 'fields' => [
            'company' => studio_field('Empresa', 'text', true, 180), 'start_date' => studio_field('Fecha de inicio', 'date', false, 10),
            'end_date' => studio_field('Fecha de finalización', 'date', false, 10, 'Déjala vacía si es tu trabajo actual. La duración se calcula automáticamente.'),
            'sort_order' => $order, 'is_active' => $active,
        ], 'extra' => ['tags' => studio_field('Tecnologías y competencias', 'text', false, 1500, 'Separa las etiquetas con comas: SAP, ABAP, Laravel, Docker.')], 'i18n' => ['table' => 'portfolio_experience_i18n', 'foreign' => 'experience_id', 'fields' => [
            'title' => studio_field('Cargo', 'text', true, 500), 'period' => studio_field('Periodo visible', 'text', true, 180, 'Por ejemplo: 2026 - Actualidad'), 'summary' => studio_field('Funciones y responsabilidades', 'textarea', true, 12000),
        ]]],
        'exp_project' => ['section' => 'experience', 'label' => 'Proyecto de esta etapa', 'table' => 'portfolio_experience_projects', 'title' => 'text_value_es', 'parent' => ['entity' => 'experience', 'field' => 'experience_id'], 'fields' => [
            'url' => studio_field('Enlace opcional', 'url'), 'sort_order' => $order, 'is_active' => $active,
        ], 'i18n' => ['table' => 'portfolio_experience_project_i18n', 'foreign' => 'project_id', 'fields' => [
            'text_value' => studio_field('Qué hiciste', 'textarea', true, 6000), 'url_label' => studio_field('Texto del enlace', 'text', false, 500),
        ]]],
        'hero_card' => ['section' => 'hero', 'label' => 'Especialidad', 'table' => 'portfolio_hero_cards', 'title' => 'label', 'fields' => [
            'label' => studio_field('Nombre visible', 'text', true, 120), 'name' => studio_field('Identificador interno', 'text', true, 80), 'sort_order' => $order, 'is_active' => $active,
        ]],
        'hero_item' => ['section' => 'hero', 'label' => 'Enlace de especialidad', 'table' => 'portfolio_hero_card_items', 'title' => 'label', 'parent' => ['entity' => 'hero_card', 'field' => 'card_id'], 'fields' => [
            'label' => studio_field('Texto del enlace', 'text', true, 120), 'url' => studio_field('Destino', 'url', true, 255, 'Usa #experience, #projects, una ruta interna o una URL https://.'), 'sort_order' => $order, 'is_active' => $active,
        ]],
        'text' => ['section' => 'texts', 'label' => 'Texto', 'table' => 'portfolio_texts', 'fields' => [], 'text' => true],
    ];
}

function studio_text_defaults(): array
{
    $defaults = require APP_BASE_PATH . '/data/translations.php';
    $editorial = require APP_BASE_PATH . '/data/editorial.php';
    foreach (['es', 'en'] as $lang) {
        $defaults[$lang]['hero_typewriter'] = $lang === 'es' ? 'Hola, soy José Ángel. Desarrollo soluciones, aplicaciones y webs que venden.' : 'Hi, I’m José Ángel. I build software, applications and websites that sell.';
        foreach ($editorial[$lang] as $key => $value) {
            foreach (is_array($value) ? $value : ['' => $value] as $index => $text) {
                $defaults[$lang]['editorial_' . $key . ($index === '' ? '' : '_' . $index)] = str_replace('\\n', "\n", $text);
            }
        }
    }
    return $defaults;
}

function studio_text_label(string $key): string
{
    $labels = [
        'meta_description' => 'Descripción para buscadores', 'hero_typewriter' => 'Frase de presentación',
        'nav_about' => 'Menú · Sobre mí', 'nav_experience' => 'Menú · Experiencia', 'nav_services' => 'Menú · Servicios', 'nav_projects' => 'Menú · Proyectos', 'nav_hobby' => 'Menú · Hobbies', 'nav_contact' => 'Menú · Contacto',
        'hero_eyebrow' => 'Disponibilidad para proyectos', 'hero_view_projects' => 'Botón · Ver proyectos', 'hero_download_cv' => 'Botón · Descargar CV', 'hero_generate_cv_pdf' => 'Botón · Generar PDF', 'hero_open_remote' => 'Disponibilidad remota',
        'about_title' => 'Título · Sobre mí', 'about_text' => 'Presentación profesional', 'studies_title' => 'Título · Formación', 'experience_title' => 'Título · Experiencia', 'experience_projects' => 'Etiqueta · Proyectos clave', 'experience_toggle' => 'Botón · Ver detalles',
        'services_title' => 'Título · Servicios', 'projects_title' => 'Título · Proyectos', 'hobby_title' => 'Título · Hobbies', 'projects_visit' => 'Botón · Visitar proyecto', 'contact_title' => 'Título de contacto', 'contact_text' => 'Texto de contacto', 'contact_email' => 'Botón · Email', 'contact_linkedin' => 'Botón · LinkedIn', 'footer_copy' => 'Derechos del pie de página',
        'editorial_headline_0' => 'Portada · Titular, línea 1', 'editorial_headline_1' => 'Portada · Titular, línea 2', 'editorial_headline_2' => 'Portada · Titular, línea 3',
        'editorial_about_heading' => 'Sobre mí · Gran titular', 'editorial_contact_heading' => 'Contacto · Gran titular', 'editorial_hobby_statement' => 'Hobbies · Gran titular',
        'editorial_work_note' => 'Proyectos · Introducción', 'editorial_signature' => 'Firma del portfolio', 'editorial_intro' => 'Servicios · Introducción',
    ];
    return $labels[$key] ?? ucfirst(str_replace('_', ' · ', str_replace('editorial_', '', $key)));
}
