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
