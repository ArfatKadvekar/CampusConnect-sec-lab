<?php
/**
 * CampusConnect Security Lab - Application Configuration
 *
 * EDUCATIONAL USE ONLY
 * This is a deliberately vulnerable security lab for teaching purposes.
 * It represents a fictional student organization and must only be run
 * in an isolated local environment.
 */

// ─────────────────────────────────────────────────────────────────────────────
//  LAB MODE
//  When LAB_MODE is TRUE the application uses weak authentication so that
//  the instructor can demonstrate the impact of poor security practices.
//  Set to FALSE to enable the hardened configuration for comparison.
// ─────────────────────────────────────────────────────────────────────────────
define('LAB_MODE', true);   // true = vulnerable | false = hardened

// ─────────────────────────────────────────────────────────────────────────────
//  SITE SETTINGS
// ─────────────────────────────────────────────────────────────────────────────
define('SITE_NAME',    'CampusConnect');
define('SITE_TAGLINE', 'Connect. Learn. Build.');
define('SITE_URL',     'http://localhost/campusconnect-security-lab');

// ─────────────────────────────────────────────────────────────────────────────
//  FICTIONAL ADMIN CREDENTIALS
//  These are entirely fictional. In LAB_MODE the password is intentionally
//  weak to allow the authentication-security demonstration.
//  In hardened mode a strong password is required.
// ─────────────────────────────────────────────────────────────────────────────
define('ADMIN_USERNAME', 'admin');

if (LAB_MODE) {
    // Weak — used to demonstrate the vulnerability
    define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));
} else {
    // Strong — used to demonstrate the hardened configuration
    // Change this to any strong passphrase before running hardened demos
    define('ADMIN_PASSWORD_HASH', password_hash('Xk9#mP2$vL7qN4!rT', PASSWORD_DEFAULT));
}

// ─────────────────────────────────────────────────────────────────────────────
//  RATE LIMITING  (only enforced when LAB_MODE = false)
// ─────────────────────────────────────────────────────────────────────────────
define('MAX_LOGIN_ATTEMPTS',  5);   // failures before lockout
define('LOCKOUT_DURATION',   15);   // minutes

// ─────────────────────────────────────────────────────────────────────────────
//  SESSION
// ─────────────────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME', 3600);   // seconds

// ─────────────────────────────────────────────────────────────────────────────
//  TIMEZONE
// ─────────────────────────────────────────────────────────────────────────────
date_default_timezone_set('Asia/Kolkata');
