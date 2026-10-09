<?php
/**
 * CampusConnect Security Lab — Demo API
 *
 * Provides RESTful JSON actions for the Instructor Dashboard.
 * Strictly restricted to localhost (127.0.0.1) and authorized instructor sessions.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/demo_helper.php';

enforce_localhost_only();

header('Content-Type: application/json; charset=utf-8');

// Ensure instructor authorization
if (!is_instructor_authorized()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Instructor authorization required.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

try {
    $db = get_db();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
//  ACTION ROUTER
// ─────────────────────────────────────────────────────────────────────────────

switch ($action) {

    // ── 1. Fetch Demo Accounts & System Status ──────────────────────────────
    case 'status':
    case 'get_accounts':
        $accounts = get_demo_accounts_list($db);
        $sim_results = $_SESSION['demo_sim_results'] ?? [];

        echo json_encode([
            'success'      => true,
            'csrf_token'   => get_demo_csrf_token(),
            'accounts'     => $accounts,
            'sim_results'  => $sim_results,
            'lab_mode'     => LAB_MODE,
            'server_time'  => date('Y-m-d H:i:s'),
        ]);
        break;

    // ── 2. Single Login Attempt (Experiment 2: Online Auth Test) ─────────────
    case 'login_attempt':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!verify_demo_csrf_token($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $scenario = in_array($_POST['scenario'] ?? '', ['insecure', 'protected']) ? $_POST['scenario'] : 'insecure';

        // Strictly restrict to authorized fictional demonstration accounts (Prevent arbitrary target testing)
        $allowed_demo_users = ['demo_student_01', 'demo_student_02', 'demo_student_03', 'demo_student_04'];
        if (!in_array($username, $allowed_demo_users, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Authentication tests are strictly restricted to authorized fictional demonstration accounts.']);
            exit;
        }

        $result = process_demo_login($db, $username, $password, $scenario, $ip);
        echo json_encode($result);
        break;

    // ── 3. Controlled Batch Authentication (Lockout Threshold Demo) ─────────
    case 'batch_attempts':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!verify_demo_csrf_token($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? 'wrongPassword123');
        $scenario = in_array($_POST['scenario'] ?? '', ['insecure', 'protected']) ? $_POST['scenario'] : 'protected';
        $count    = min(10, max(1, (int)($_POST['count'] ?? 5)));

        // Strictly restrict to authorized fictional demonstration accounts
        $allowed_demo_users = ['demo_student_01', 'demo_student_02', 'demo_student_03', 'demo_student_04'];
        if (!in_array($username, $allowed_demo_users, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Authentication tests are strictly restricted to authorized fictional demonstration accounts.']);
            exit;
        }

        $batch_results = [];
        for ($i = 1; $i <= $count; $i++) {
            $attempt_pass = $password . ($i > 1 ? "_test{$i}" : '');
            $res = process_demo_login($db, $username, $attempt_pass, $scenario, $ip);
            $res['attempt_index'] = $i;
            $batch_results[] = $res;

            // Small deliberate pause so browser sees distinct timestamps
            usleep(25000); // 25ms
        }

        $account = get_demo_account_by_username($db, $username);

        echo json_encode([
            'success'       => true,
            'username'      => $username,
            'scenario'      => $scenario,
            'count'         => $count,
            'batch_results' => $batch_results,
            'final_account' => $account,
        ]);
        break;

    // ── 4. Multi-Factor Authentication (MFA) Verification Step ──────────────
    case 'mfa_verify':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!verify_demo_csrf_token($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $otp = trim($_POST['otp'] ?? '');
        $account = get_demo_account_by_username($db, $username);

        if (!$account) {
            echo json_encode(['success' => false, 'message' => 'Account not found.']);
            exit;
        }

        // Demo OTP code is 849201 or 123456
        $valid_otps = ['849201', '123456', '519283', '629405', '739104'];
        if (in_array($otp, $valid_otps, true)) {
            echo json_encode([
                'success' => true,
                'message' => "MFA Code Accepted! Two-Factor Authentication complete for {$account['display_name']}.",
                'account' => $account
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid or expired 6-digit MFA verification code. (Hint for demo: 849201 or 123456)',
            ]);
        }
        break;

    // ── 5. Fetch Server Audit Logs ──────────────────────────────────────────
    case 'get_logs':
        $logs = get_recent_demo_attempts($db, 30);
        echo json_encode([
            'success' => true,
            'logs'    => $logs,
        ]);
        break;

    // ── 6. Record Simulation Results (Experiment 1 Comparison) ──────────────
    case 'record_sim_result':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!verify_demo_csrf_token($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
            exit;
        }

        $account_code = strtoupper(trim($_POST['account_code'] ?? ''));
        $evaluated    = (int)($_POST['evaluated'] ?? 0);
        $total        = (int)($_POST['total'] ?? 25001);
        $elapsed_ms   = (float)($_POST['elapsed_ms'] ?? 0);
        $found        = !empty($_POST['found']);
        $position     = isset($_POST['position']) && is_numeric($_POST['position']) ? (int)$_POST['position'] : null;
        $rate         = (float)($_POST['rate'] ?? 0);

        if (!isset($_SESSION['demo_sim_results'])) {
            $_SESSION['demo_sim_results'] = [];
        }

        $_SESSION['demo_sim_results'][$account_code] = [
            'account_code' => $account_code,
            'evaluated'    => $evaluated,
            'total'        => $total,
            'elapsed_ms'   => $elapsed_ms,
            'found'        => $found,
            'position'     => $position,
            'rate'         => $rate,
            'recorded_at'  => date('H:i:s'),
        ];

        echo json_encode([
            'success'     => true,
            'sim_results' => $_SESSION['demo_sim_results'],
        ]);
        break;

    // ── 7. Reset Demonstration State (Disposable Lab Reset) ─────────────────
    case 'reset_demo':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!verify_demo_csrf_token($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
            exit;
        }

        reset_demo_state($db);

        echo json_encode([
            'success'  => true,
            'message'  => 'Demonstration state reset successfully. All demo accounts unlocked and disposable logs cleared.',
            'accounts' => get_demo_accounts_list($db),
            'sim_results' => [],
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown API action.']);
        break;
}
