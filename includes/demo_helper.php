<?php
/**
 * CampusConnect Security Lab — Demo Helper
 *
 * Dedicated logic for the Multi-User Password Security Demonstration.
 * EDUCATIONAL USE ONLY — Strictly for Localhost Demonstration (127.0.0.1)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

// ─────────────────────────────────────────────────────────────────────────────
//  Localhost & Security Guard
// ─────────────────────────────────────────────────────────────────────────────

function is_demo_localhost(): bool
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = ['127.0.0.1', '::1', 'localhost', '::ffff:127.0.0.1'];

    if (in_array($remote, $allowed, true)) {
        return true;
    }

    // CLI or local server
    if (php_sapi_name() === 'cli' || php_sapi_name() === 'cli-server') {
        return true;
    }

    return false;
}

function enforce_localhost_only(): void
{
    if (!is_demo_localhost()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head>';
        echo '<body style="font-family: sans-serif; text-align: center; padding: 50px;">';
        echo '<h1 style="color: #dc2626;">403 — Access Forbidden</h1>';
        echo '<p>The CampusConnect Password Security Demonstration is strictly restricted to local lab use on <code>127.0.0.1</code>.</p>';
        echo '<p>External network access is prohibited for lab safety.</p>';
        echo '</body></html>';
        exit;
    }
}

function is_instructor_authorized(): bool
{
    // Authorized if logged in as Admin or if an instructor session is established
    return is_admin_logged_in() || !empty($_SESSION['instructor_authorized']);
}

function require_instructor_authorization(): void
{
    enforce_localhost_only();

    if (!is_instructor_authorized()) {
        header('Location: ' . SITE_URL . '/lab-demo/login.php');
        exit;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  CSRF Helpers
// ─────────────────────────────────────────────────────────────────────────────

function get_demo_csrf_token(): string
{
    if (empty($_SESSION['demo_csrf_token'])) {
        $_SESSION['demo_csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['demo_csrf_token'];
}

function verify_demo_csrf_token(?string $token): bool
{
    if (empty($token) || empty($_SESSION['demo_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['demo_csrf_token'], $token);
}

// ─────────────────────────────────────────────────────────────────────────────
//  Wordlist Path
// ─────────────────────────────────────────────────────────────────────────────

function get_demo_wordlist_path(): string
{
    $primary = __DIR__ . '/../data/campusconnect_demo_wordlist.txt';
    if (file_exists($primary)) {
        return $primary;
    }
    $secondary = __DIR__ . '/../../campusconnect_demo_wordlist.txt';
    if (file_exists($secondary)) {
        return $secondary;
    }
    return $primary;
}

// ─────────────────────────────────────────────────────────────────────────────
//  Database Setup & Seeding Helpers
// ─────────────────────────────────────────────────────────────────────────────

function ensure_demo_tables(PDO $db): void
{
    // Create demo_accounts if not exists
    $db->exec("
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
        ) ENGINE=InnoDB
    ");

    // Create demo_login_attempts if not exists
    $db->exec("
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
        ) ENGINE=InnoDB
    ");

    // Check if demo accounts already seeded
    $count = (int) $db->query("SELECT COUNT(*) FROM demo_accounts")->fetchColumn();
    if ($count < 4) {
        $stmt = $db->prepare("
            INSERT INTO demo_accounts 
                (account_code, username, display_name, category, password_length, is_predictable, target_password, password_hash, scenario_mode, is_locked, failed_attempts, locked_until, login_demonstrated, mfa_enabled, mfa_secret)
            VALUES
                ('A', 'demo_student_01', 'Student Account A', 'Predictable (Campus-Themed)', 10, 1, 'campus2026', ?, 'insecure', 0, 0, NULL, 0, 0, 'DEMO-OTP-849201'),
                ('B', 'demo_student_02', 'Student Account B', 'Modified Pattern (Capital + Symbol + Year)', 11, 1, 'Campus@2026', ?, 'insecure', 0, 0, NULL, 0, 0, 'DEMO-OTP-739104'),
                ('C', 'demo_student_03', 'Student Account C', 'Random Generated String (High Entropy)', 16, 0, 'rN7#kP9!wB2\$xT5&', ?, 'protected', 0, 0, NULL, 0, 1, 'DEMO-OTP-519283'),
                ('D', 'demo_student_04', 'Student Account D', 'Diceware Multi-Word Passphrase', 24, 0, 'falcon-river-blue-matrix', ?, 'protected', 0, 0, NULL, 0, 1, 'DEMO-OTP-629405')
            ON DUPLICATE KEY UPDATE
                display_name = VALUES(display_name),
                category = VALUES(category),
                password_length = VALUES(password_length),
                is_predictable = VALUES(is_predictable),
                target_password = VALUES(target_password),
                password_hash = VALUES(password_hash),
                mfa_secret = VALUES(mfa_secret)
        ");

        $hA = password_hash('campus2026', PASSWORD_BCRYPT);
        $hB = password_hash('Campus@2026', PASSWORD_BCRYPT);
        $hC = password_hash('rN7#kP9!wB2$xT5&', PASSWORD_BCRYPT);
        $hD = password_hash('falcon-river-blue-matrix', PASSWORD_BCRYPT);

        $stmt->execute([$hA, $hB, $hC, $hD]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  Demo Account Data Access
// ─────────────────────────────────────────────────────────────────────────────

function get_demo_accounts_list(PDO $db): array
{
    ensure_demo_tables($db);

    $stmt = $db->query("SELECT * FROM demo_accounts ORDER BY account_code ASC");
    $accounts = $stmt->fetchAll();

    $now = time();
    foreach ($accounts as &$acc) {
        // Calculate remaining lockout seconds if locked
        if (!empty($acc['is_locked']) && !empty($acc['locked_until'])) {
            $unlock_ts = strtotime($acc['locked_until']);
            if ($unlock_ts > $now) {
                $acc['lockout_remaining_seconds'] = $unlock_ts - $now;
                $acc['lockout_remaining_minutes'] = (int) ceil(($unlock_ts - $now) / 60);
            } else {
                // Lock has expired; automatically release
                $acc['is_locked'] = 0;
                $acc['locked_until'] = null;
                $acc['failed_attempts'] = 0;
                $update = $db->prepare("UPDATE demo_accounts SET is_locked = 0, locked_until = NULL, failed_attempts = 0 WHERE id = ?");
                $update->execute([$acc['id']]);
            }
        } else {
            $acc['lockout_remaining_seconds'] = 0;
            $acc['lockout_remaining_minutes'] = 0;
        }

        // Expected position in candidate dataset
        switch ($acc['account_code']) {
            case 'A':
                $acc['candidate_position'] = 4999;
                $acc['in_dataset'] = true;
                break;
            case 'B':
                $acc['candidate_position'] = 12000;
                $acc['in_dataset'] = true;
                break;
            case 'C':
                $acc['candidate_position'] = null;
                $acc['in_dataset'] = false;
                break;
            case 'D':
                $acc['candidate_position'] = null;
                $acc['in_dataset'] = false;
                break;
            default:
                $acc['candidate_position'] = null;
                $acc['in_dataset'] = false;
        }
    }
    unset($acc);

    return $accounts;
}

function get_demo_account_by_code(PDO $db, string $code): ?array
{
    ensure_demo_tables($db);
    $stmt = $db->prepare("SELECT * FROM demo_accounts WHERE account_code = ? LIMIT 1");
    $stmt->execute([strtoupper($code)]);
    $acc = $stmt->fetch();
    return $acc ?: null;
}

function get_demo_account_by_username(PDO $db, string $username): ?array
{
    ensure_demo_tables($db);
    $stmt = $db->prepare("SELECT * FROM demo_accounts WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $acc = $stmt->fetch();
    return $acc ?: null;
}

// ─────────────────────────────────────────────────────────────────────────────
//  Demo Reset Helper
// ─────────────────────────────────────────────────────────────────────────────

function reset_demo_state(PDO $db): void
{
    ensure_demo_tables($db);

    // Reset demo accounts to clean initial state
    $db->exec("
        UPDATE demo_accounts 
           SET is_locked = 0,
               failed_attempts = 0,
               locked_until = NULL,
               login_demonstrated = 0
    ");

    // Clean demo attempts log ONLY (preserves regular registrations & participant logs!)
    $db->exec("TRUNCATE TABLE demo_login_attempts");

    // Clear session-recorded simulation comparison results
    unset($_SESSION['demo_sim_results']);
}

// ─────────────────────────────────────────────────────────────────────────────
//  Demo Authentication Processor (Experiment 2)
// ─────────────────────────────────────────────────────────────────────────────

function process_demo_login(PDO $db, string $username, string $password, string $scenario, string $ip): array
{
    ensure_demo_tables($db);

    $start_time = microtime(true);
    $account = get_demo_account_by_username($db, $username);

    // Default response shape
    $response = [
        'success'           => false,
        'message'           => '',
        'scenario'          => $scenario,
        'username'          => $username,
        'is_locked'         => false,
        'failed_attempts'   => 0,
        'lockout_remaining' => 0,
        'mfa_required'      => false,
        'controls_active'   => [],
        'control_triggered' => 'None',
        'response_time_ms'  => 0.00,
        'http_code'         => 200,
    ];

    if ($scenario === 'insecure') {
        // ── SCENARIO A: INSECURE AUTHENTICATION ──────────────────────────────
        // Weaknesses demonstrated:
        // 1. User enumeration (verbose error message discloses if user exists)
        // 2. No rate limiting or lockout
        // 3. Fast comparison without computational cost factor
        // 4. No MFA challenge

        $response['controls_active'] = [
            'Rate Limiting'   => 'Disabled',
            'Account Lockout' => 'Disabled',
            'Password Hashing'=> 'Plain / Weak Check',
            'Error Messages'  => 'Verbose (User Enumeration Vulnerable)',
            'MFA Challenge'   => 'Disabled'
        ];

        if (!$account) {
            $response['message'] = "Authentication Error: Username '{$username}' does not exist in student database.";
            $response['control_triggered'] = 'User Enumeration Disclosure';
            $response['http_code'] = 401;
        } else {
            // Check password directly
            $is_match = ($password === $account['target_password']);

            if ($is_match) {
                $response['success'] = true;
                $response['message'] = "Success: Logged in as {$account['display_name']} ({$username}). Notice: No delay or MFA requested.";
                $response['control_triggered'] = 'Direct Password Match';

                // Mark login demonstrated
                $stmt = $db->prepare("UPDATE demo_accounts SET login_demonstrated = 1 WHERE id = ?");
                $stmt->execute([$account['id']]);
            } else {
                $response['message'] = "Authentication Error: Password incorrect for user '{$username}'. Attempt count unrestricted.";
                $response['control_triggered'] = 'Verbose Password Mismatch';
                $response['http_code'] = 401;
            }
        }

    } else {
        // ── SCENARIO B: PROTECTED AUTHENTICATION ────────────────────────────
        // Defenses demonstrated:
        // 1. Generic error messages (prevents user enumeration)
        // 2. Server-side bcrypt hashing with computational delay
        // 3. Rate limiting and account lockout after 5 failed attempts (15 min)
        // 4. Session regeneration on success
        // 5. Optional MFA challenge step

        $response['controls_active'] = [
            'Rate Limiting'   => 'Enforced (Max 5 attempts)',
            'Account Lockout' => '15 Minutes Lockout',
            'Password Hashing'=> 'Bcrypt (Cost 10, Timing Hardened)',
            'Error Messages'  => 'Generic Uniform Error',
            'MFA Challenge'   => 'Enforced for High-Security Accounts'
        ];

        // 1. Check if account is currently locked
        if ($account && $account['is_locked']) {
            $locked_until_ts = strtotime($account['locked_until'] ?? '');
            $now = time();

            if ($locked_until_ts > $now) {
                $remaining_min = (int) ceil(($locked_until_ts - $now) / 60);
                $response['is_locked'] = true;
                $response['failed_attempts'] = (int) $account['failed_attempts'];
                $response['lockout_remaining'] = $remaining_min;
                $response['message'] = "Security Notice: Account '{$username}' is locked due to 5 consecutive failed attempts. Retry in {$remaining_min} minute(s).";
                $response['control_triggered'] = 'Account Lockout Enforced';
                $response['http_code'] = 429; // Too Many Requests

                $elapsed = (microtime(true) - $start_time) * 1000;
                $response['response_time_ms'] = round($elapsed, 2);

                // Log locked attempt
                log_demo_login_attempt($db, $account['id'], $username, $ip, 'protected', $password, false, 'Account Lockout Enforced', $response['response_time_ms']);
                return $response;
            } else {
                // Unlock expired
                $account['is_locked'] = 0;
                $account['failed_attempts'] = 0;
                $account['locked_until'] = null;
                $db->prepare("UPDATE demo_accounts SET is_locked = 0, failed_attempts = 0, locked_until = NULL WHERE id = ?")->execute([$account['id']]);
            }
        }

        // 2. Perform credential check with generic timing
        // Always run password_verify or dummy verify to avoid timing discrepancy
        $dummy_hash = '$2y$10$wU0M7Z8yD9gZ5rP2sX9J7eF9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O';
        $target_hash = $account ? $account['password_hash'] : $dummy_hash;
        $is_valid = password_verify($password, $target_hash);

        if ($account && $is_valid) {
            // Password verified successfully!
            // Check if MFA is required
            if (!empty($account['mfa_enabled'])) {
                $response['success'] = true;
                $response['mfa_required'] = true;
                $response['message'] = "Primary Credentials Accepted. Multi-Factor Authentication (MFA) step required.";
                $response['control_triggered'] = 'Password Verified + MFA Required';
            } else {
                $response['success'] = true;
                $response['message'] = "Success: Authenticated as {$account['display_name']} ({$username}). Protected with bcrypt and rate limits.";
                $response['control_triggered'] = 'Password Verified (Protected)';
            }

            // Reset failed attempts on success
            $db->prepare("UPDATE demo_accounts SET failed_attempts = 0, login_demonstrated = 1 WHERE id = ?")->execute([$account['id']]);

        } else {
            // Failed credentials
            $response['http_code'] = 401;
            $response['message'] = 'Invalid username or password.'; // Uniform generic error message

            if ($account) {
                $new_failed = (int) $account['failed_attempts'] + 1;
                $response['failed_attempts'] = $new_failed;

                if ($new_failed >= 5) {
                    // Trigger lockout for 15 minutes
                    $lock_until = date('Y-m-d H:i:s', time() + (15 * 60));
                    $stmt = $db->prepare("UPDATE demo_accounts SET failed_attempts = ?, is_locked = 1, locked_until = ? WHERE id = ?");
                    $stmt->execute([$new_failed, $lock_until, $account['id']]);

                    $response['is_locked'] = true;
                    $response['lockout_remaining'] = 15;
                    $response['message'] = "Account locked: Maximum failed login threshold (5) reached. Locked for 15 minutes.";
                    $response['control_triggered'] = 'Lockout Threshold Triggered (5th Failure)';
                    $response['http_code'] = 429;
                } else {
                    $stmt = $db->prepare("UPDATE demo_accounts SET failed_attempts = ? WHERE id = ?");
                    $stmt->execute([$new_failed, $account['id']]);
                    $response['control_triggered'] = "Generic Error (Attempt {$new_failed}/5)";
                }
            } else {
                $response['control_triggered'] = 'Generic Error (User Not Found)';
            }
        }
    }

    $elapsed = (microtime(true) - $start_time) * 1000;
    $response['response_time_ms'] = round($elapsed, 2);

    // Record login attempt in demo_login_attempts
    $acc_id = $account ? (int)$account['id'] : null;
    log_demo_login_attempt(
        $db, 
        $acc_id, 
        $username, 
        $ip, 
        $scenario, 
        $password, 
        $response['success'], 
        $response['control_triggered'], 
        $response['response_time_ms']
    );

    return $response;
}

function log_demo_login_attempt(
    PDO $db,
    ?int $account_id,
    string $username,
    string $ip,
    string $scenario,
    string $attempted_password,
    bool $success,
    string $control_triggered,
    float $response_time_ms
): void {
    try {
        // Mask password before logging for safety (never log plain password in logs)
        $masked_pass = strlen($attempted_password) > 0 ? (substr($attempted_password, 0, 2) . '***') : '';

        $stmt = $db->prepare("
            INSERT INTO demo_login_attempts 
                (demo_account_id, username, ip_address, scenario_mode, attempted_password, success, control_triggered, response_time_ms)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $account_id,
            $username,
            $ip,
            $scenario,
            $masked_pass,
            $success ? 1 : 0,
            $control_triggered,
            $response_time_ms
        ]);
    } catch (PDOException $e) {
        // Silent fail
    }
}

function get_recent_demo_attempts(PDO $db, int $limit = 25): array
{
    ensure_demo_tables($db);
    $stmt = $db->prepare("
        SELECT a.*, d.display_name, d.account_code
          FROM demo_login_attempts a
     LEFT JOIN demo_accounts d ON d.id = a.demo_account_id
      ORDER BY a.attempted_at DESC, a.id DESC
         LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
