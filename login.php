<?php
/**
 * CampusConnect Security Lab — Participant Login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_participant_logged_in()) {
    header('Location: ' . SITE_URL . '/');
    exit;
}

$page_title = 'Login — ' . SITE_NAME;
$active_nav = 'login';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } elseif (participant_login($email, $password)) {
        header('Location: ' . SITE_URL . '/');
        exit;
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="form-page">
    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-card-header">
            <div class="form-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h1 class="form-card-title">Welcome Back</h1>
            <p class="form-card-subtitle">Log in to access your registrations and event details.</p>
        </div>

        <form method="POST" action="" data-validate>
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       placeholder="Enter your password"
                       required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-lg form-submit" id="login-submit">
                Log In
            </button>
        </form>

        <div class="form-footer">
            Don't have an account? <a href="<?= SITE_URL ?>/register.php">Register here</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
