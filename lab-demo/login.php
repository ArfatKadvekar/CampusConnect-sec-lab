<?php
/**
 * CampusConnect Security Lab — Instructor Demonstration Access Gateway
 *
 * Provides access control to the instructor dashboard.
 * Enforces localhost restriction (127.0.0.1) and authorization credentials.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/demo_helper.php';

enforce_localhost_only();

// If already admin or authorized, redirect straight to dashboard
if (is_instructor_authorized()) {
    header('Location: ' . SITE_URL . '/lab-demo/index.php');
    exit;
}

$error = '';
$info  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Allow admin credentials or direct instructor lab login
    $is_admin = ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH));
    $is_instructor_quick = ($username === 'instructor' && in_array($password, ['demo1234', 'admin123', 'campus2026'], true));

    if ($is_admin || $is_instructor_quick) {
        session_regenerate_id(true);
        $_SESSION['instructor_authorized'] = true;
        $_SESSION['instructor_user']       = $username;
        $_SESSION['instructor_login_at']   = time();
        header('Location: ' . SITE_URL . '/lab-demo/index.php');
        exit;
    } else {
        $error = 'Invalid credentials for instructor presentation mode.';
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
        <strong>Session Note:</strong> You may authenticate using the administrative credentials (<code>admin</code>) or use instructor demonstration credentials.
    </div>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label" for="username" style="font-size: 0.85rem; font-weight: 600;">Instructor / Admin Username</label>
            <input type="text"
                   id="username"
                   name="username"
                   class="form-control"
                   placeholder="e.g. admin or instructor"
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
