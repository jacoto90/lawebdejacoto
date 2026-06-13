# LaWebDeJacoto

Professional bilingual portfolio for Jose Angel Colmena Tomas.

## Highlights

- ES/EN language switch
- Mobile-first responsive layout
- Component-based PHP structure
- Recruiter-focused experience timeline
- Downloadable CV with professional filename
- Hidden admin access prepared for live portfolio editing
- Secure PHP/MySQL admin bootstrap for Hostalia

## Stack

- PHP (component rendering)
- CSS (custom UI system)
- Vanilla JavaScript (menu, animations, interactions)
- Composer (project bootstrap/autoload)
- MySQL/MariaDB via PDO for the admin panel

## Project Structure

- `index.php` main entry
- `admin/` private administration entry
- `app/` internal PHP helpers for database, sessions and auth
- `.env.example` safe template for local database credentials
- `config/database.example.php` safe template for Hostalia MySQL credentials
- `components/` UI sections
- `data/profile.php` profile, experience, projects
- `data/translations.php` ES/EN text map
- `assets/css/main.css` design system and responsive styles
- `assets/js/main.js` UI behavior

## Admin Panel Status

The first admin phases are implemented.

- Hidden access: tap/click 3 times on the `Python / Odoo` skill chip.
- Direct admin URL: `/admin/`.
- First visit with an empty admin table shows the initial admin registration form.
- Once one admin user exists, registration is closed and only login is available.
- Authenticated users can open `/admin/content.php` from the dashboard.
- The public portfolio reads from the database when editable content exists.
- If database access fails, the public portfolio falls back to the original PHP arrays.

Editable areas:

- Profile/contact data: name, email, phone, LinkedIn, CV, photos.
- Hero bilingual role, location, age and bio.
- Hobby bilingual title/text and image path.
- General UI/SEO texts from `data/translations.php`.
- Skill chips.
- Hero metrics/highlights.
- Studies.
- Services.
- Featured projects.
- Experience entries, tags and key projects.

When the admin tables are empty, current content is seeded from:

- `data/profile.php`
- `data/translations.php`

English fields are editable. If an English field is left empty in a CRUD form, the Spanish value is copied as a safe fallback so the public site never renders empty required text. API-based automatic translation is still a future enhancement.

## Admin Security

- Passwords are stored with `password_hash()`.
- SQL access uses PDO prepared statements with emulated prepares disabled.
- Forms use CSRF tokens.
- Sessions use strict mode, HTTP-only cookies and `SameSite=Strict`.
- Session IDs regenerate periodically and after login.
- Login and setup forms are rate-limited in the database.
- After 5 failed attempts, the identifier is locked for 15 minutes.
- Initial setup requires a private `setup_key` from `config/database.php`.
- `/app` and `/config` are blocked from direct HTTP access through `.htaccess`.
- Real database credentials must not be committed. `config/database.php` is ignored by Git.

The triple tap is only a hidden entry point, not the security layer. Real protection is the login, CSRF, rate limiting, session hardening and database protections.

## Local Database Setup

For local testing, create `.env` from `.env.example`:

```env
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_DATABASE=storage/local-admin.sqlite
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_AUTO_CREATE=true
ADMIN_SETUP_KEY=use-a-long-private-random-string-here
```

By default local development uses SQLite so the admin panel can be tested without running MySQL. The local database file is created at `storage/local-admin.sqlite` and is ignored by Git.

If you want to test locally with MySQL, change `DB_CONNECTION=mysql` and set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`. With `DB_AUTO_CREATE=true`, the local MySQL database is created automatically if the user has permissions.

`.env` is ignored by Git and must not be committed.

## Hostalia Database Setup

Create a MySQL/MariaDB database in Hostalia. You can either use Hostalia values in `.env`, or create this private PHP config file:

```php
<?php

return [
    'host' => 'localhost',
    'database' => 'your_database_name',
    'username' => 'your_database_user',
    'password' => 'your_database_password',
    'charset' => 'utf8mb4',
    'auto_create' => false,
    'setup_key' => 'use-a-long-private-random-string-here',
];
```

Save it as:

- `config/database.php`

There is a safe template at:

- `config/database.example.php`

When `/admin/` loads with valid credentials, the admin tables are created automatically if they do not exist.
The first admin registration form asks for `setup_key`. This prevents someone else from creating the first admin if the database is still empty.

Created tables:

- `admin_users`
- `admin_login_attempts`
- `portfolio_meta`
- `portfolio_profile`
- `portfolio_profile_i18n`
- `portfolio_texts`
- `portfolio_skills`
- `portfolio_highlights`
- `portfolio_highlight_i18n`
- `portfolio_studies`
- `portfolio_study_i18n`
- `portfolio_experiences`
- `portfolio_experience_i18n`
- `portfolio_experience_tags`
- `portfolio_experience_projects`
- `portfolio_experience_project_i18n`
- `portfolio_services`
- `portfolio_service_i18n`
- `portfolio_projects`
- `portfolio_project_i18n`

## Next Admin Phases

- Add media/PDF upload management instead of editing file paths manually.
- Add API-based ES to EN automatic translation with review before publishing.
- Add editable content for the logistics showcase landing.
- Add audit log for admin changes.

## Run Locally

Use your local PHP server (or Herd) and open the project root.

Example with PHP built-in server:

```bash
php -S localhost:8080
```

Then visit:

- `http://localhost:8080/index.php?lang=es`
- `http://localhost:8080/index.php?lang=en`

## Branches

- `main` stable branch
- `developer` working branch
