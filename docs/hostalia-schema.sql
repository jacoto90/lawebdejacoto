-- LaWebDeJacoto admin schema for Hostalia MySQL/MariaDB
-- Import this into database 10840863_jacotadas only if automatic migrations cannot run.

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(32) NOT NULL,
    identifier_hash CHAR(64) NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_attempt_at DATETIME NOT NULL,
    UNIQUE KEY uniq_action_identifier (action, identifier_hash),
    KEY idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_meta (
    meta_key VARCHAR(80) PRIMARY KEY,
    meta_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_profile (
    id VARCHAR(16) PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(80) NOT NULL,
    linkedin VARCHAR(255) NOT NULL,
    cv VARCHAR(255) NOT NULL,
    cv_download_name VARCHAR(255) NOT NULL,
    photo VARCHAR(255) NOT NULL,
    golf_photo VARCHAR(255) NOT NULL,
    updated_at VARCHAR(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_profile_i18n (
    profile_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    role TEXT NOT NULL,
    location TEXT NOT NULL,
    age TEXT NOT NULL,
    bio TEXT NOT NULL,
    hobby_title TEXT NOT NULL,
    hobby_text TEXT NOT NULL,
    PRIMARY KEY (profile_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_texts (
    text_key VARCHAR(80) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    text_value TEXT NOT NULL,
    PRIMARY KEY (text_key, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_skills (
    id VARCHAR(16) PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_highlights (
    id VARCHAR(16) PRIMARY KEY,
    metric VARCHAR(40) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_highlight_i18n (
    highlight_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    label TEXT NOT NULL,
    PRIMARY KEY (highlight_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_studies (
    id VARCHAR(16) PRIMARY KEY,
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_study_i18n (
    study_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    period TEXT NOT NULL,
    title TEXT NOT NULL,
    center TEXT NOT NULL,
    PRIMARY KEY (study_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_experiences (
    id VARCHAR(16) PRIMARY KEY,
    company VARCHAR(180) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_experience_i18n (
    experience_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    period TEXT NOT NULL,
    title TEXT NOT NULL,
    summary TEXT NOT NULL,
    PRIMARY KEY (experience_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_experience_tags (
    id VARCHAR(16) PRIMARY KEY,
    experience_id VARCHAR(16) NOT NULL,
    name VARCHAR(80) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_experience_projects (
    id VARCHAR(16) PRIMARY KEY,
    experience_id VARCHAR(16) NOT NULL,
    url VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_experience_project_i18n (
    project_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    text_value TEXT NOT NULL,
    url_label TEXT NOT NULL,
    PRIMARY KEY (project_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_services (
    id VARCHAR(16) PRIMARY KEY,
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_service_i18n (
    service_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (service_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_projects (
    id VARCHAR(16) PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    logo VARCHAR(40) NOT NULL,
    url VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_project_i18n (
    project_id VARCHAR(16) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    type TEXT NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (project_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
