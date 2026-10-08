<?php
/**
 * CampusConnect Security Lab — Homepage
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = SITE_NAME . ' — Events & Workshops';
$active_nav = 'home';

// Fetch upcoming events
try {
    $db = get_db();
    $stmt = $db->query(
        'SELECT * FROM events WHERE is_active = 1 ORDER BY event_date ASC LIMIT 4'
    );
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// Fetch quick stats
try {
    $total_participants = $db->query('SELECT COUNT(*) FROM participants')->fetchColumn();
    $total_events       = $db->query('SELECT COUNT(*) FROM events WHERE is_active = 1')->fetchColumn();
    $total_regs         = $db->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
} catch (PDOException $e) {
    $total_participants = '180+';
    $total_events       = 4;
    $total_regs         = 200;
}

$event_icons = ['🌐', '🐧', '🔐', '⚡'];
$event_icon_classes = ['icon-blue', 'icon-teal', 'icon-purple', 'icon-orange'];
$event_tags  = ['Workshop', 'Session', 'Session', 'Bootcamp'];

include __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ───────────────────────────────────────────────────────────────── -->
<section class="hero" id="home">
    <div class="hero-grid"></div>
    <div class="hero-container">
        <div class="hero-badge">Registrations Open — 2026</div>

        <h1 class="hero-title">
            <span class="highlight">Connect. Learn.</span><br>Build Together.
        </h1>

        <p class="hero-subtitle">
            CampusConnect brings students together through technical events, workshops, and hands-on sessions.
            Grow your skills. Meet your community.
        </p>

        <div class="hero-actions">
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-lg">
                🎟 Register for an Event
            </a>
            <a href="#events" class="btn btn-secondary btn-lg">
                Browse Events
            </a>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-num" data-count="<?= (int)$total_participants ?>" data-suffix="">
                    <?= (int)$total_participants ?>
                </span>
                <span class="hero-stat-label">Registered Students</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num" data-count="<?= (int)$total_events ?>" data-suffix="">
                    <?= (int)$total_events ?>
                </span>
                <span class="hero-stat-label">Upcoming Events</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num" data-count="<?= (int)$total_regs ?>" data-suffix="+">
                    <?= (int)$total_regs ?>+
                </span>
                <span class="hero-stat-label">Total Registrations</span>
            </div>
        </div>
    </div>
</section>

<!-- ── Events ─────────────────────────────────────────────────────────────── -->
<section class="section" id="events" style="background: var(--clr-bg-2);">
    <div class="section-container">
        <div class="section-header">
            <div class="section-tag">Upcoming</div>
            <h2 class="section-title">Events &amp; Workshops</h2>
            <p class="section-subtitle">
                Hands-on sessions designed for students at every level — from complete beginners to enthusiasts.
            </p>
        </div>

        <div class="events-grid">
            <?php if (empty($events)): ?>
                <p class="text-muted" style="grid-column: 1/-1; text-align: center; padding: 40px 0;">
                    No upcoming events at the moment. Check back soon!
                </p>
            <?php else: ?>
                <?php foreach ($events as $i => $event): ?>
                <div class="event-card" data-animate data-event-id="<?= $event['id'] ?>">
                    <div class="event-card-icon <?= $event_icon_classes[$i % 4] ?>">
                        <?= $event_icons[$i % 4] ?>
                    </div>
                    <div class="event-card-tag"><?= $event_tags[$i % 4] ?></div>
                    <h3 class="event-card-title"><?= htmlspecialchars($event['title']) ?></h3>
                    <p class="event-card-desc"><?= htmlspecialchars($event['description']) ?></p>
                    <div class="event-card-meta">
                        <span>
                            <span class="meta-icon">📅</span>
                            <?= date('D, d M Y', strtotime($event['event_date'])) ?>
                            &nbsp;·&nbsp;
                            <?= date('h:i A', strtotime($event['event_time'])) ?>
                        </span>
                        <span>
                            <span class="meta-icon">📍</span>
                            <?= htmlspecialchars($event['venue']) ?>
                        </span>
                        <span>
                            <span class="meta-icon">👥</span>
                            Limited to <?= $event['capacity'] ?> seats
                        </span>
                    </div>
                    <a href="<?= SITE_URL ?>/register.php?event=<?= $event['id'] ?>" class="btn btn-accent btn-sm">
                        Register Now →
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── Workshops section ──────────────────────────────────────────────────── -->
<section class="section" id="workshops">
    <div class="section-container">
        <div class="section-header">
            <div class="section-tag">Hands-on</div>
            <h2 class="section-title">Why Join Our Workshops?</h2>
            <p class="section-subtitle">
                Every session is designed by students, for students — practical, fun, and beginner-friendly.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
            <?php
            $features = [
                ['🛠', 'Hands-on Learning', 'Every workshop includes practice exercises and real projects you can add to your portfolio.'],
                ['🤝', 'Peer Community',    'Learn alongside fellow students. Collaborate, ask questions, and grow together.'],
                ['🏅', 'Certificates',     'Earn a digital certificate of participation for every event you attend.'],
                ['📚', 'Free Resources',   'All workshop materials, slides, and code samples are shared with participants.'],
            ];
            foreach ($features as [$icon, $title, $desc]):
            ?>
            <div class="event-card" style="display: flex; flex-direction: column; gap: 14px;">
                <div style="font-size: 2rem;"><?= $icon ?></div>
                <h3 style="font-family: var(--font-display); font-size: 1rem; font-weight: 700; color: var(--clr-text);"><?= $title ?></h3>
                <p style="font-size: .87rem; color: var(--clr-text-muted); line-height: 1.65;"><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── About ──────────────────────────────────────────────────────────────── -->
<section class="section" id="about" style="background: var(--clr-bg-2);">
    <div class="section-container">
        <div class="about-grid">
            <div class="about-content">
                <div class="section-tag">About Us</div>
                <h2 class="section-title mt-0" style="text-align: left;">A Community Built by Students</h2>
                <p style="color: var(--clr-text-muted); line-height: 1.75; margin-bottom: 8px;">
                    CampusConnect is a student-run technical community that organizes events, workshops, and collaborative
                    sessions throughout the academic year. Our goal is simple: make learning accessible and engaging for everyone.
                </p>
                <p style="color: var(--clr-text-muted); line-height: 1.75;">
                    Whether you're a first-year curious about programming or a senior looking to sharpen your skills,
                    there's a place for you here.
                </p>

                <div class="about-features">
                    <?php
                    $abt = [
                        ['🎯', 'Skill-focused events', 'Each event targets a specific skill area to maximize learning impact.'],
                        ['🌱', 'Beginner-friendly', 'All sessions start from the basics — no prior experience needed.'],
                        ['📡', 'Open to all branches', 'Any student from any department is welcome to attend and contribute.'],
                    ];
                    foreach ($abt as [$icon, $title, $desc]):
                    ?>
                    <div class="about-feature">
                        <div class="about-feature-icon"><?= $icon ?></div>
                        <div class="about-feature-text">
                            <h4><?= $title ?></h4>
                            <p><?= $desc ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="about-visual">
                <div class="stat-card stat-card--accent">
                    <div class="stat-card-num"><?= (int)$total_participants ?>+</div>
                    <div class="stat-card-label">Students Registered</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-num"><?= (int)$total_events ?></div>
                    <div class="stat-card-label">Events This Semester</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-num">4</div>
                    <div class="stat-card-label">Departments Covered</div>
                </div>
                <div class="stat-card stat-card--accent">
                    <div class="stat-card-num">100%</div>
                    <div class="stat-card-label">Free to Attend</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA ────────────────────────────────────────────────────────────────── -->
<section style="padding: 80px 24px; text-align: center; background: var(--clr-bg);">
    <div style="max-width: 560px; margin: 0 auto;">
        <h2 style="font-family: var(--font-display); font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--clr-text); letter-spacing: -1px; margin-bottom: 14px;">
            Ready to get started?
        </h2>
        <p style="color: var(--clr-text-muted); margin-bottom: 32px; line-height: 1.7;">
            Join hundreds of students who are learning, building, and growing with CampusConnect.
            Registration is free and takes less than a minute.
        </p>
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-lg">
            Create Your Account →
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
