-- ─────────────────────────────────────────────────────────────────────────────
--  CampusConnect Security Lab — Demo Schema & Seed Data
--  EDUCATIONAL USE ONLY — Disposable Synthetic Demo Accounts
--  Exclusively for Localhost Demonstration (127.0.0.1)
-- ─────────────────────────────────────────────────────────────────────────────

USE campusconnect_lab;

-- ── Demo Accounts Table ──────────────────────────────────────────────────────
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

-- ── Demo Login Attempts Table ────────────────────────────────────────────────
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

-- ── Seed Demo Accounts (Clean Insert / Update) ──────────────────────────────
INSERT INTO demo_accounts 
    (account_code, username, display_name, category, password_length, is_predictable, target_password, password_hash, scenario_mode, is_locked, failed_attempts, locked_until, login_demonstrated, mfa_enabled, mfa_secret)
VALUES
(
    'A',
    'demo_student_01',
    'Student Account A',
    'Predictable (Campus-Themed)',
    10,
    1,
    'campus2026',
    '$2y$10$wU0M7Z8yD9gZ5rP2sX9J7eF9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O',
    'insecure',
    0,
    0,
    NULL,
    0,
    0,
    'DEMO-OTP-849201'
),
(
    'B',
    'demo_student_02',
    'Student Account B',
    'Modified Pattern (Capital + Symbol + Year)',
    11,
    1,
    'Campus@2026',
    '$2y$10$tV1N8A9zE0hA6sQ3tY0K8fG0B2C3D4E5F6G7H8I9J0K1L2M3N4O5P',
    'insecure',
    0,
    0,
    NULL,
    0,
    0,
    'DEMO-OTP-739104'
),
(
    'C',
    'demo_student_03',
    'Student Account C',
    'Random Generated String (High Entropy)',
    16,
    0,
    'rN7#kP9!wB2$xT5&',
    '$2y$10$uW2O9B0aF1iB7tR4uZ1L9gH1C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q',
    'protected',
    0,
    0,
    NULL,
    0,
    1,
    'DEMO-OTP-519283'
),
(
    'D',
    'demo_student_04',
    'Student Account D',
    'Diceware Multi-Word Passphrase',
    24,
    0,
    'falcon-river-blue-matrix',
    '$2y$10$vX3P0C1bG2jC8uS5va2M0hI2D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R',
    'protected',
    0,
    0,
    NULL,
    0,
    1,
    'DEMO-OTP-629405'
)
ON DUPLICATE KEY UPDATE
    display_name = VALUES(display_name),
    category = VALUES(category),
    password_length = VALUES(password_length),
    is_predictable = VALUES(is_predictable),
    target_password = VALUES(target_password),
    password_hash = VALUES(password_hash),
    mfa_secret = VALUES(mfa_secret);
