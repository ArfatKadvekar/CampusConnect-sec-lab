<?php
/**
 * CampusConnect Security Lab — Admin Login
 *
 * This page is intentionally publicly reachable (no obfuscation).
 * In LAB_MODE the credentials are weak, demonstrating the vulnerability.
 * In hardened mode rate limiting and lockout are applied.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already authenticated
if (is_admin_logged_in()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$error   = '';
$warning = '';
$locked  = false;

// ── Lab-mode informational banner ─────────────────────────────────────────────
// This is only shown to the instructor to confirm which mode is active.
// It does NOT expose credentials.
$lab_mode_active = LAB_MODE;

// ── Handle login attempt ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username and password.';
    } else {
        $result = admin_login($username, $password);

        if ($result['success']) {
            header('Location: ' . SITE_URL . '/admin/dashboard.php');
            exit;
        }

        $locked = $result['locked'] ?? false;
        $error  = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="admin-body">

<div class="admin-login-page">
    <div class="admin-login-card">

        <!-- ── Brand header ────────────────────────────────────────────────── -->
        <div class="admin-login-header">
            <div class="admin-login-logo">
                <div class="brand-icon">CC</div>
                <span class="brand-name" style="font-size: 1.1rem;"><?= SITE_NAME ?></span>
            </div>
            <h1 class="admin-login-title">Administration</h1>
            <p class="admin-login-sub">Sign in to access the event management dashboard.</p>
        </div>

        <!-- ── Instructor note (lab mode indicator — no credentials shown) ── -->
        <?php if ($lab_mode_active): ?>
        <div class="alert alert-warn" style="margin-bottom: 20px; font-size: .82rem;">
            ⚠️ <strong>Lab Mode Active</strong> — Weak authentication is enabled for this demonstration.
            Set <code>LAB_MODE = false</code> in <code>config/config.php</code> to enable hardened mode.
        </div>
        <?php else: ?>
        <div class="alert alert-info" style="margin-bottom: 20px; font-size: .82rem;">
            🔒 <strong>Hardened Mode Active</strong> — Rate limiting and account lockout are enabled.
        </div>
        <?php endif; ?>

        <!-- ── Flash messages ───────────────────────────────────────────────── -->
        <?php if ($error): ?>
            <div class="alert <?= $locked ? 'alert-warn' : 'alert-error' ?>">
                <?= $locked ? '🔒' : '❌' ?> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- ── Login form ───────────────────────────────────────────────────── -->
        <div class="admin-form">
            <form method="POST" action="" id="admin-login-form">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           class="form-control"
                           placeholder="Enter username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           autocomplete="username"
                           <?= $locked ? 'disabled' : '' ?>
                           autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           placeholder="Enter password"
                           autocomplete="current-password"
                           <?= $locked ? 'disabled' : '' ?>>
                </div>

                <button type="submit"
                        class="btn btn-primary form-submit"
                        id="admin-login-btn"
                        <?= $locked ? 'disabled' : '' ?>>
                    <?= $locked ? '🔒 Account Locked' : 'Sign In →' ?>
                </button>
            </form>
        </div>

        <p style="text-align: center; margin-top: 20px; font-size: .8rem; color: var(--clr-text-faint);">
            <a href="<?= SITE_URL ?>/" style="color: var(--clr-text-faint);">← Back to CampusConnect</a>
        </p>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
</body>
</html>
