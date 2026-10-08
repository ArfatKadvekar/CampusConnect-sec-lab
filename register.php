<?php
/**
 * CampusConnect Security Lab — Event Registration
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Register — ' . SITE_NAME;
$active_nav = 'events';

$errors   = [];
$success  = false;
$selected_event = (int)($_GET['event'] ?? 0);

// Fetch events for dropdown
try {
    $db = get_db();
    $stmt = $db->query('SELECT id, title, event_date FROM events WHERE is_active = 1 ORDER BY event_date ASC');
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

$branches = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil', 'Electrical', 'Chemical', 'Other'];
$years    = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];

// ── Handle submission ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $branch   = trim($_POST['branch']   ?? '');
    $year     = (int)($_POST['year']    ?? 0);
    $event_id = (int)($_POST['event_id']?? 0);

    // Validate
    if (empty($name))            $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6)  $errors[] = 'Password must be at least 6 characters.';
    if (empty($branch))         $errors[] = 'Please select your branch.';
    if ($year < 1 || $year > 4) $errors[] = 'Please select your year.';
    if ($event_id < 1)          $errors[] = 'Please select an event to register for.';

    if (empty($errors)) {
        try {
            // Check duplicate email
            $chk = $db->prepare('SELECT id FROM participants WHERE email = ?');
            $chk->execute([$email]);
            $existing = $chk->fetchColumn();

            if ($existing) {
                // Existing participant — just add registration
                $participant_id = $existing;
            } else {
                // New participant
                $ins = $db->prepare(
                    'INSERT INTO participants (name, email, password, branch, year) VALUES (?, ?, ?, ?, ?)'
                );
                $ins->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $branch, $year]);
                $participant_id = $db->lastInsertId();
            }

            // Register for event (ignore duplicate)
            $reg = $db->prepare(
                'INSERT IGNORE INTO registrations (participant_id, event_id) VALUES (?, ?)'
            );
            $reg->execute([$participant_id, $event_id]);

            $success = true;

        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- ── Page hero ──────────────────────────────────────────────────────────── -->
<div class="page-hero">
    <div class="section-tag">Join Us</div>
    <h1 class="page-hero-title">Event Registration</h1>
    <p class="page-hero-sub">Fill in your details to register for an upcoming CampusConnect event.</p>
</div>

<!-- ── Registration form ──────────────────────────────────────────────────── -->
<div class="form-page" style="min-height: auto; padding: 60px 24px;">
    <div class="form-card form-card--wide">

        <?php if ($success): ?>
            <div class="alert alert-success" data-auto-dismiss="6000">
                ✅ You have successfully registered! Check your email for event details.
            </div>
            <div style="text-align: center; margin-top: 16px;">
                <a href="<?= SITE_URL ?>/" class="btn btn-secondary">← Back to Home</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary" style="margin-left: 12px;">Register for Another</a>
            </div>
        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <div>
                        <strong>Please fix the following:</strong>
                        <ul style="margin-top: 6px; padding-left: 18px;">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-card-header">
                <div class="form-card-icon">🎟</div>
                <h2 class="form-card-title">Register for an Event</h2>
                <p class="form-card-subtitle">All events are free to attend. A registration confirmation will be sent to your email.</p>
            </div>

            <form method="POST" action="" data-validate>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control"
                               placeholder="e.g. Rohan Mehta"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address *</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               placeholder="you@example.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Create a Password *</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           placeholder="Minimum 6 characters"
                           required>
                    <p class="form-hint">This will be used to log in and manage your registrations.</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="branch">Branch / Department *</label>
                        <select id="branch" name="branch" class="form-control" required>
                            <option value="">Select branch…</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b ?>" <?= (($_POST['branch'] ?? '') === $b) ? 'selected' : '' ?>>
                                    <?= $b ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="year">Year of Study *</label>
                        <select id="year" name="year" class="form-control" required>
                            <option value="">Select year…</option>
                            <?php foreach ($years as $val => $label): ?>
                                <option value="<?= $val ?>" <?= (($_POST['year'] ?? 0) == $val) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="event_id">Event to Register For *</label>
                    <select id="event_id" name="event_id" class="form-control" required>
                        <option value="">Select an event…</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>"
                                <?= (((int)($_POST['event_id'] ?? $selected_event)) === (int)$ev['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['title']) ?>
                                — <?= date('d M Y', strtotime($ev['event_date'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="divider"></div>

                <button type="submit" class="btn btn-primary btn-lg form-submit" id="register-submit">
                    Register →
                </button>
            </form>

            <div class="form-footer">
                Already registered? <a href="<?= SITE_URL ?>/login.php">Log in to your account</a>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
