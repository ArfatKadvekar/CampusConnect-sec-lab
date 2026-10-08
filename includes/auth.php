<?php
/**
 * CampusConnect Security Lab — Authentication helper
 *
 * Handles participant sessions and admin session validation.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ─────────────────────────────────────────────────────────────────────────────
//  Session bootstrap
// ─────────────────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ─────────────────────────────────────────────────────────────────────────────
//  Participant authentication
// ─────────────────────────────────────────────────────────────────────────────

function participant_login(string $email, string $password): bool
{
    try {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id, name, password FROM participants WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['participant_id']   = $user['id'];
            $_SESSION['participant_name'] = $user['name'];
            return true;
        }
    } catch (PDOException $e) {
        // Silently fail — production would log this
    }
    return false;
}

function is_participant_logged_in(): bool
{
    return isset($_SESSION['participant_id']);
}

function participant_logout(): void
{
    unset($_SESSION['participant_id'], $_SESSION['participant_name']);
}

// ─────────────────────────────────────────────────────────────────────────────
//  Admin authentication
// ─────────────────────────────────────────────────────────────────────────────

function admin_login(string $username, string $password): array
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // ── Hardened mode: check rate limit before verifying credentials ──────────
    if (!LAB_MODE) {
        $check = check_rate_limit($ip);
        if ($check['locked']) {
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please wait ' . $check['remaining'] . ' minute(s) before trying again.',
                'locked'  => true,
            ];
        }
    }

    $success = (
        $username === ADMIN_USERNAME &&
        password_verify($password, ADMIN_PASSWORD_HASH)
    );

    // ── Log every attempt when hardened ───────────────────────────────────────
    if (!LAB_MODE) {
        log_admin_attempt($ip, $username, $success);
    }

    if ($success) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = ADMIN_USERNAME;
        $_SESSION['admin_login_at']  = time();
        return ['success' => true, 'message' => 'Welcome back, Administrator.'];
    }

    return [
        'success' => false,
        'message' => 'Invalid username or password.',
        'locked'  => false,
    ];
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    }
}

function admin_logout(): void
{
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_user'], $_SESSION['admin_login_at']);
    session_destroy();
}

// ─────────────────────────────────────────────────────────────────────────────
//  Rate limiting helpers  (hardened mode only)
// ─────────────────────────────────────────────────────────────────────────────

function check_rate_limit(string $ip): array
{
    try {
        $db = get_db();
        $cutoff = date('Y-m-d H:i:s', time() - (LOCKOUT_DURATION * 60));

        $stmt = $db->prepare(
            'SELECT COUNT(*) AS cnt FROM admin_login_attempts
              WHERE ip_address = ? AND success = 0 AND attempted_at >= ?'
        );
        $stmt->execute([$ip, $cutoff]);
        $row = $stmt->fetch();

        if ((int) $row['cnt'] >= MAX_LOGIN_ATTEMPTS) {
            // Find the earliest recent failure to calculate remaining lockout
            $stmt2 = $db->prepare(
                'SELECT MIN(attempted_at) AS first_fail FROM admin_login_attempts
                  WHERE ip_address = ? AND success = 0 AND attempted_at >= ?'
            );
            $stmt2->execute([$ip, $cutoff]);
            $row2 = $stmt2->fetch();

            $unlock_at = strtotime($row2['first_fail']) + (LOCKOUT_DURATION * 60);
            $remaining = max(1, (int) ceil(($unlock_at - time()) / 60));

            return ['locked' => true, 'remaining' => $remaining];
        }
    } catch (PDOException $e) {
        // Fall through — don't block access on DB error
    }

    return ['locked' => false, 'remaining' => 0];
}

function log_admin_attempt(string $ip, string $username, bool $success): void
{
    try {
        $db   = get_db();
        $stmt = $db->prepare(
            'INSERT INTO admin_login_attempts (ip_address, username, success) VALUES (?, ?, ?)'
        );
        $stmt->execute([$ip, $username, $success ? 1 : 0]);
    } catch (PDOException $e) {
        // Logging is non-critical — silently ignore
    }
}
