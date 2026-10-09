<?php
/**
 * CampusConnect — Instructor Demonstration Access Gateway
 *
 * Provides access control to the password-security demonstration dashboard.
 * Enforces localhost restriction (127.0.0.1) and verifies admin credentials.
 *
 * SECURITY NOTE: Only the real administrator account (ADMIN_USERNAME / ADMIN_PASSWORD_HASH
 * defined in config/config.php) may unlock this page. No shortcut backdoor credentials exist.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/demo_helper.php';

enforce_localhost_only();

// If already admin or a valid instructor session exists, redirect to dashboard
if (is_instructor_authorized()) {
    header('Location: ' . SITE_URL . '/lab-demo/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Only the real admin account unlocks the instructor presentation dashboard.
    // No hardcoded shortcut credentials are permitted for security demonstration integrity.
    $is_admin = ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH));

    if ($is_admin) {
        session_regenerate_id(true);
        $_SESSION['instructor_authorized'] = true;
        $_SESSION['instructor_user']       = $username;
        $_SESSION['instructor_login_at']   = time();
        header('Location: ' . SITE_URL . '/lab-demo/index.php');
        exit;
    } else {
        $error = 'Invalid admin credentials. Please use the administrator username and password configured in config/config.php.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Authorization — Password Security Lab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        .instructor-auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            padding: 24px;
        }
        .instructor-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            max-width: 440px;
            width: 100%;
            padding: 36px 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .badge-localhost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #bfdbfe;
            margin-bottom: 16px;
        }
        .badge-dot {
            width: 7px;
            height: 7px;
            background: #2563eb;
            border-radius: 50%;
        }
        .instructor-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px 0;
        }
        .instructor-sub {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0 0 24px 0;
            line-height: 1.5;
        }
        .notice-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 20px;
            line-height: 1.45;
        }
    </style>
</head>
<body class="instructor-auth-page">

<div class="instructor-card">
    <div class="badge-localhost">
        <span class="badge-dot"></span>
        <span>Localhost Lab Restricted (127.0.0.1)</span>
    </div>

    <h1 class="instructor-title">Instructor Presentation Access</h1>
    <p class="instructor-sub">
        Sign in to open the Multi-User Password Security Demonstration Dashboard for classroom projection.
    </p>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="notice-box">
        <strong>Session Note:</strong> Authenticate using the administrator credentials configured in <code>config/config.php</code> (<code>ADMIN_USERNAME</code> / <code>ADMIN_PASSWORD_HASH</code>). This page is only accessible from <code>127.0.0.1</code>.
    </div>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label" for="username" style="font-size: 0.85rem; font-weight: 600;">Admin Username</label>
            <input type="text"
                   id="username"
                   name="username"
                   class="form-control"
                   placeholder="e.g. admin"
                   value="admin"
                   required
                   autofocus>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label" for="password" style="font-size: 0.85rem; font-weight: 600;">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="Enter password"
                   required>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">
            Launch Demonstration Dashboard →
        </button>
    </form>

    <div style="margin-top: 24px; text-align: center; font-size: 0.8rem; color: #94a3b8;">
        <a href="<?= SITE_URL ?>/" style="color: #64748b; text-decoration: none;">← Return to CampusConnect Portal</a>
    </div>
</div>

</body>
</html>
