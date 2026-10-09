-- ─────────────────────────────────────────────────────────────────────────────
--  CampusConnect Security Lab — Database Schema
--  EDUCATIONAL USE ONLY — Fictional data only
-- ─────────────────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS campusconnect_lab
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE campusconnect_lab;

-- ─────────────────────────────────────────────────────────────────────────────
--  Events table
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200)    NOT NULL,
    slug        VARCHAR(200)    NOT NULL UNIQUE,
    description TEXT,
    event_date  DATE            NOT NULL,
    event_time  TIME            DEFAULT '10:00:00',
    venue       VARCHAR(200)    DEFAULT 'Seminar Hall A',
    capacity    INT UNSIGNED    DEFAULT 60,
    is_active   TINYINT(1)      DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────────
--  Participants table  (fictional users only)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS participants (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    email       VARCHAR(200)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    branch      VARCHAR(100),
    year        TINYINT UNSIGNED,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────────
--  Registrations table
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS registrations (
    id             INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    participant_id INT UNSIGNED    NOT NULL,
    event_id       INT UNSIGNED    NOT NULL,
    registered_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    status         ENUM('confirmed','waitlisted','cancelled') DEFAULT 'confirmed',
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id)       REFERENCES events(id)       ON DELETE CASCADE,
    UNIQUE KEY uq_registration (participant_id, event_id)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────────
--  Admin login attempts  (used in hardened mode)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_login_attempts (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    ip_address  VARCHAR(45)     NOT NULL,
    username    VARCHAR(100),
    attempted_at TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    success     TINYINT(1)      DEFAULT 0
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────────
--  Demo accounts for Password Security Demonstration
--  EDUCATIONAL USE ONLY — Disposable synthetic accounts
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS demo_accounts (
    id                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    account_code        VARCHAR(10)     NOT NULL UNIQUE,
    username            VARCHAR(100)    NOT NULL UNIQUE,
    display_name        VARCHAR(150)    NOT NULL,
    category            VARCHAR(100)    NOT NULL,
    password_length     INT UNSIGNED    NOT NULL,
    is_predictable      TINYINT(1)      NOT NULL DEFAULT 1,
    target_password     VARCHAR(255)    NOT NULL,
    password_hash       VARCHAR(255)    NOT NULL,
    scenario_mode       ENUM('insecure','protected') DEFAULT 'insecure',
    is_locked           TINYINT(1)      DEFAULT 0,
    failed_attempts     INT UNSIGNED    DEFAULT 0,
    locked_until        TIMESTAMP       NULL DEFAULT NULL,
    login_demonstrated  TINYINT(1)      DEFAULT 0,
    mfa_enabled         TINYINT(1)      DEFAULT 0,
    mfa_secret          VARCHAR(32)     DEFAULT 'DEMO-OTP-849201',
    created_at          TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────────────────────
--  Demo login attempts log (isolated from admin/participant logs)
-- ─────────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS demo_login_attempts (
    id                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    demo_account_id     INT UNSIGNED    NULL,
    username            VARCHAR(100)    NOT NULL,
    ip_address          VARCHAR(45)     NOT NULL,
    scenario_mode       ENUM('insecure','protected') NOT NULL,
    attempted_password  VARCHAR(255)    NULL,
    attempted_at        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    success             TINYINT(1)      DEFAULT 0,
    control_triggered   VARCHAR(100)    DEFAULT 'None',
    response_time_ms    DECIMAL(8, 2)   DEFAULT 0.00,
    FOREIGN KEY (demo_account_id) REFERENCES demo_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

