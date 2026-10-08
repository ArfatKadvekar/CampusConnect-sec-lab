<?php
/**
 * CampusConnect Security Lab — Admin Dashboard
 *
 * Protected page: only accessible after successful admin authentication.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin_login();   // Redirects to /admin/ if not authenticated

$page_title = 'Dashboard — ' . SITE_NAME . ' Administration';

// ── Fetch dashboard data ──────────────────────────────────────────────────────
try {
    $db = get_db();

    $total_participants = (int) $db->query('SELECT COUNT(*) FROM participants')->fetchColumn();
    $total_events       = (int) $db->query('SELECT COUNT(*) FROM events WHERE is_active = 1')->fetchColumn();
    $total_regs         = (int) $db->query('SELECT COUNT(*) FROM registrations')->fetchColumn();

    // Recent registrations (last 12)
    $recent_regs = $db->query(
        'SELECT r.id, p.name, p.email, p.branch, e.title AS event_title,
                r.registered_at, r.status
           FROM registrations r
           JOIN participants p ON p.id = r.participant_id
           JOIN events e       ON e.id = r.event_id
          ORDER BY r.registered_at DESC
          LIMIT 12'
    )->fetchAll();

    // Upcoming events with registration counts
    $upcoming_events = $db->query(
        'SELECT e.*, COUNT(r.id) AS reg_count
           FROM events e
           LEFT JOIN registrations r ON r.event_id = e.id
          WHERE e.is_active = 1
          GROUP BY e.id
          ORDER BY e.event_date ASC'
    )->fetchAll();

    // All participants with their event count
    $all_participants = $db->query(
        'SELECT p.*, COUNT(r.id) AS event_count
           FROM participants p
           LEFT JOIN registrations r ON r.participant_id = p.id
          GROUP BY p.id
          ORDER BY p.created_at DESC'
    )->fetchAll();

    // Admin login attempts log (hardened mode)
    $login_attempts = [];
    if (!LAB_MODE) {
        $login_attempts = $db->query(
            'SELECT * FROM admin_login_attempts ORDER BY attempted_at DESC LIMIT 20'
        )->fetchAll();
    }

} catch (PDOException $e) {
    $total_participants = 0;
    $total_events       = 0;
    $total_regs         = 0;
    $recent_regs        = [];
    $upcoming_events    = [];
    $all_participants   = [];
    $login_attempts     = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        /* Additional dashboard-specific styles */
        .tab-bar {
            display: flex;
            gap: 4px;
            margin-bottom: 24px;
            background: var(--clr-surface);
            padding: 6px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--clr-border);
            width: fit-content;
        }

        .tab-btn {
            padding: 8px 18px;
            border-radius: var(--radius-md);
            font-size: .85rem;
            font-weight: 600;
            color: var(--clr-text-muted);
            cursor: pointer;
            border: none;
            background: none;
            transition: all var(--trans-fast);
        }

        .tab-btn.active, .tab-btn:hover {
            background: var(--clr-surface-2);
            color: var(--clr-text);
        }

        .tab-btn.active {
            color: var(--clr-primary-lt);
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .attempt-row-fail { background: rgba(255,90,110,.04); }
        .attempt-row-success { background: rgba(50,213,131,.04); }
    </style>
</head>
<body class="admin-body">

<div class="admin-layout">

    <!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <div class="brand-icon brand-icon--sm">CC</div>
            <div>
                <div class="admin-sidebar-brand"><?= SITE_NAME ?></div>
                <div class="admin-sidebar-sub">Administration</div>
            </div>
        </div>

        <nav class="admin-nav">
            <div class="admin-nav-section">
                <div class="admin-nav-label">Overview</div>
                <a href="#" class="admin-nav-link active" onclick="showTab('overview', this)">
                    <span class="icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                    </span> Dashboard
                </a>
                <a href="#" class="admin-nav-link" onclick="showTab('events-tab', this)">
                    <span class="icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </span> Events
                </a>
            </div>

            <div class="admin-nav-section">
                <div class="admin-nav-label">Participants</div>
                <a href="#" class="admin-nav-link" onclick="showTab('participants-tab', this)">
                    <span class="icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span> All Participants
                </a>
                <a href="#" class="admin-nav-link" onclick="showTab('regs-tab', this)">
                    <span class="icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </span> Registrations
                </a>
            </div>

            <?php if (!LAB_MODE): ?>
            <div class="admin-nav-section">
                <div class="admin-nav-label">Security</div>
                <a href="#" class="admin-nav-link" onclick="showTab('security-tab', this)">
                    <span class="icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span> Login Attempts
                </a>
            </div>
            <?php endif; ?>

            <div class="admin-nav-section">
                <div class="admin-nav-label">Site</div>
                <a href="<?= SITE_URL ?>/" class="admin-nav-link" target="_blank">
                    <span class="icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </span> View Website
                </a>
            </div>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="<?= SITE_URL ?>/admin/logout.php" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">
                Sign Out
            </a>
        </div>
    </aside>

    <!-- ── Main content ─────────────────────────────────────────────────────── -->
    <main class="admin-main">

        <!-- Top bar -->
        <div class="admin-topbar">
            <div class="admin-topbar-title" id="topbar-title">Dashboard Overview</div>
            <div class="admin-topbar-right">
                <div class="admin-user-badge">
                    <div class="admin-user-avatar">A</div>
                    <span><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></span>
                </div>
            </div>
        </div>

        <div class="admin-content">

            <!-- ════════════════════════════════════════════════════════════════
                 TAB: OVERVIEW / DASHBOARD
                 ════════════════════════════════════════════════════════════════ -->
            <div id="tab-overview" class="tab-content active">

                <!-- Stats -->
                <div class="dashboard-stats">
                    <div class="dash-stat-card">
                        <div class="dash-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div class="dash-stat-value"><?= $total_participants ?></div>
                        <div class="dash-stat-label">Registered Participants</div>
                        <div class="dash-stat-change">↑ Active registrations</div>
                    </div>
                    <div class="dash-stat-card">
                        <div class="dash-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <div class="dash-stat-value"><?= $total_events ?></div>
                        <div class="dash-stat-label">Upcoming Events</div>
                        <div class="dash-stat-change">← This semester</div>
                    </div>
                    <div class="dash-stat-card">
                        <div class="dash-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <div class="dash-stat-value"><?= $total_regs ?></div>
                        <div class="dash-stat-label">Total Registrations</div>
                        <div class="dash-stat-change">↑ Across all events</div>
                    </div>
                    <div class="dash-stat-card">
                        <div class="dash-stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div class="dash-stat-value"><?= count($recent_regs) ?></div>
                        <div class="dash-stat-label">Recent (Last 12)</div>
                        <div class="dash-stat-change">← Most recent</div>
                    </div>
                </div>

                <!-- Grid: Recent Regs + Upcoming Events -->
                <div class="dashboard-grid">

                    <!-- Recent registrations table -->
                    <div class="dash-panel">
                        <div class="dash-panel-header">
                            <span class="dash-panel-title">Recent Registrations</span>
                            <span class="dash-panel-action" onclick="showTab('regs-tab')">View All →</span>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Participant</th>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_regs)): ?>
                                        <tr><td colspan="4" style="text-align:center; padding: 24px; color: var(--clr-text-faint);">No registrations yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_regs as $reg): ?>
                                        <tr>
                                            <td>
                                                <span class="name-cell"><?= htmlspecialchars($reg['name']) ?></span>
                                                <br>
                                                <span style="font-size:.75rem; color: var(--clr-text-faint);"><?= htmlspecialchars($reg['branch'] ?? '') ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($reg['event_title']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $reg['status'] === 'confirmed' ? 'success' : 'warn' ?>">
                                                    <?= ucfirst($reg['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M y', strtotime($reg['registered_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Upcoming events panel -->
                    <div class="dash-panel">
                        <div class="dash-panel-header">
                            <span class="dash-panel-title">Upcoming Events</span>
                            <span class="dash-panel-action" onclick="showTab('events-tab')">Manage →</span>
                        </div>
                        <?php foreach ($upcoming_events as $ev): ?>
                        <div class="event-list-item">
                            <div class="event-list-dot"></div>
                            <div>
                                <div class="event-list-title"><?= htmlspecialchars($ev['title']) ?></div>
                                <div class="event-list-date">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?= date('d M Y', strtotime($ev['event_date'])) ?>
                                    &nbsp;·&nbsp;
                                    <span class="badge badge-info"><?= $ev['reg_count'] ?> registered</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <!-- ════════════════════════════════════════════════════════════════
                 TAB: EVENTS
                 ════════════════════════════════════════════════════════════════ -->
            <div id="tab-events-tab" class="tab-content">
                <h2 style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; margin-bottom: 24px; color: var(--clr-text);">
                    Event Management
                </h2>
                <div class="dash-panel">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event Title</th>
                                    <th>Date</th>
                                    <th>Venue</th>
                                    <th>Capacity</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcoming_events as $ev): ?>
                                <tr>
                                    <td><?= $ev['id'] ?></td>
                                    <td><span class="name-cell"><?= htmlspecialchars($ev['title']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($ev['event_date'])) ?></td>
                                    <td><?= htmlspecialchars($ev['venue']) ?></td>
                                    <td><?= $ev['capacity'] ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $ev['reg_count'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">Active</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ════════════════════════════════════════════════════════════════
                 TAB: PARTICIPANTS
                 ════════════════════════════════════════════════════════════════ -->
            <div id="tab-participants-tab" class="tab-content">
                <h2 style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; margin-bottom: 24px; color: var(--clr-text);">
                    All Participants
                </h2>
                <div class="dash-panel">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Year</th>
                                    <th>Events</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_participants as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><span class="name-cell"><?= htmlspecialchars($p['name']) ?></span></td>
                                    <td><?= htmlspecialchars($p['email']) ?></td>
                                    <td><?= htmlspecialchars($p['branch'] ?? '—') ?></td>
                                    <td><?= $p['year'] ? 'Year ' . $p['year'] : '—' ?></td>
                                    <td><span class="badge badge-info"><?= $p['event_count'] ?></span></td>
                                    <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ════════════════════════════════════════════════════════════════
                 TAB: ALL REGISTRATIONS
                 ════════════════════════════════════════════════════════════════ -->
            <div id="tab-regs-tab" class="tab-content">
                <h2 style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; margin-bottom: 24px; color: var(--clr-text);">
                    All Registrations
                </h2>
                <div class="dash-panel">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Participant</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_regs as $reg): ?>
                                <tr>
                                    <td><?= $reg['id'] ?></td>
                                    <td><span class="name-cell"><?= htmlspecialchars($reg['name']) ?></span></td>
                                    <td><?= htmlspecialchars($reg['email']) ?></td>
                                    <td><?= htmlspecialchars($reg['branch'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($reg['event_title']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $reg['status'] === 'confirmed' ? 'success' : 'warn' ?>">
                                            <?= ucfirst($reg['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($reg['registered_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ════════════════════════════════════════════════════════════════
                 TAB: SECURITY LOG  (hardened mode only)
                 ════════════════════════════════════════════════════════════════ -->
            <?php if (!LAB_MODE): ?>
            <div id="tab-security-tab" class="tab-content">
                <h2 style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; margin-bottom: 16px; color: var(--clr-text);">
                    Admin Login Attempts
                </h2>
                <div class="alert alert-info" style="margin-bottom: 20px; font-size: .85rem;">
                    Security log: Admin authentication attempts are recorded below.
                    After <?= MAX_LOGIN_ATTEMPTS ?> failed attempts from the same IP, access is blocked for <?= LOCKOUT_DURATION ?> minutes.
                </div>
                <div class="dash-panel">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>IP Address</th>
                                    <th>Username Tried</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($login_attempts)): ?>
                                    <tr><td colspan="4" style="text-align:center; padding:24px; color: var(--clr-text-faint);">No login attempts recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($login_attempts as $att): ?>
                                    <tr class="<?= $att['success'] ? 'attempt-row-success' : 'attempt-row-fail' ?>">
                                        <td><?= date('d M Y H:i:s', strtotime($att['attempted_at'])) ?></td>
                                        <td><code style="font-size:.82rem; color: var(--clr-text-muted);"><?= htmlspecialchars($att['ip_address']) ?></code></td>
                                        <td><?= htmlspecialchars($att['username'] ?? '—') ?></td>
                                        <td>
                                            <?php if ($att['success']): ?>
                                                <span class="badge badge-success">Success</span>
                                            <?php else: ?>
                                                <span class="badge" style="background:rgba(255,90,110,.12);color:var(--clr-danger);">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /admin-content -->
    </main>
</div>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
// ── Tab navigation ────────────────────────────────────────────────────────────
function showTab(tabId, linkEl) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));

    // Show selected
    const target = document.getElementById('tab-' + tabId);
    if (target) target.classList.add('active');

    // Update sidebar active
    document.querySelectorAll('.admin-nav-link').forEach(l => l.classList.remove('active'));
    if (linkEl) linkEl.classList.add('active');

    // Update topbar title
    const titles = {
        'overview':        'Dashboard Overview',
        'events-tab':      'Event Management',
        'participants-tab':'All Participants',
        'regs-tab':        'Registrations',
        'security-tab':    'Security Log',
    };
    const titleEl = document.getElementById('topbar-title');
    if (titleEl && titles[tabId]) titleEl.textContent = titles[tabId];

    return false;   // prevent link default navigation
}
</script>

</body>
</html>
