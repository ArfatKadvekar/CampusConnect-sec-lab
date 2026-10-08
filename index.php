<?php
/**
 * CampusConnect Security Lab — Homepage
 * All PHP/DB logic preserved. Only markup restructured.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = SITE_NAME . ' — Events & Workshops';
$active_nav = 'home';

// ── Fetch upcoming events ─────────────────────────────────────────────────────
try {
    $db   = get_db();
    $stmt = $db->query(
        'SELECT * FROM events WHERE is_active = 1 ORDER BY event_date ASC LIMIT 4'
    );
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// ── Fetch summary stats ───────────────────────────────────────────────────────
try {
    $total_participants = (int) $db->query('SELECT COUNT(*) FROM participants')->fetchColumn();
    $total_events       = (int) $db->query('SELECT COUNT(*) FROM events WHERE is_active = 1')->fetchColumn();
    $total_regs         = (int) $db->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
} catch (PDOException $e) {
    $total_participants = 0;
    $total_events       = 0;
    $total_regs         = 0;
}

// ── Category labels per event slot ───────────────────────────────────────────
$categories = ['Workshop', 'Session', 'Session', 'Bootcamp'];

include __DIR__ . '/includes/header.php';
?>

<!-- ════════════════════════════════════════════════════════════════════════════
     HERO  — compact, not full-screen
     ════════════════════════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-label">Registrations open — <?= date('Y') ?></div>

        <h1 class="hero-title">
            <?= SITE_NAME ?><br>
            Student Events &amp; Technical Workshops
        </h1>

        <p class="hero-subtitle">
            Discover technical workshops, sessions, and student-led events happening on campus.
            Free to attend. Open to all branches and years.
        </p>

        <div class="hero-actions">
            <a href="#events" class="btn btn-primary">Browse Events</a>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-secondary">Register</a>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════════════
     STATS STRIP
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="stats-strip">
    <div class="stats-strip-inner">
        <div class="stat-item">
            <span class="stat-num"><?= $total_participants ?>+</span>
            <span class="stat-label">Registered Students</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num"><?= $total_events ?></span>
            <span class="stat-label">Upcoming Events</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num"><?= $total_regs ?>+</span>
            <span class="stat-label">Total Registrations</span>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════════
     UPCOMING EVENTS
     ════════════════════════════════════════════════════════════════════════════ -->
<section class="section" id="events">
    <div class="section-container">
        <div class="section-header">
            <div class="section-eyebrow">Upcoming</div>
            <h2 class="section-title">Events &amp; Workshops</h2>
            <p class="section-subtitle">
                Hands-on sessions designed for students at every level — from complete beginners to enthusiasts.
            </p>
        </div>

        <div class="events-grid">
            <?php if (empty($events)): ?>
                <p class="text-muted" style="grid-column: 1/-1; padding: 32px 0; text-align: center;">
                    No upcoming events at the moment. Check back soon.
                </p>
            <?php else: ?>
                <?php foreach ($events as $i => $event): ?>
                <div class="event-card">
                    <div class="event-card-category"><?= $categories[$i % 4] ?></div>
                    <h3 class="event-card-title"><?= htmlspecialchars($event['title']) ?></h3>
                    <p class="event-card-desc"><?= htmlspecialchars($event['description']) ?></p>

                    <div class="event-card-meta">
                        <div class="event-meta-row">
                            <!-- Calendar icon -->
                            <svg class="event-meta-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="1" y="3" width="14" height="12" rx="1.5"/>
                                <path d="M1 7h14M5 1v4M11 1v4"/>
                            </svg>
                            <?= date('D, d M Y', strtotime($event['event_date'])) ?>
                            &nbsp;&middot;&nbsp;
                            <?= date('g:i A', strtotime($event['event_time'])) ?>
                        </div>
                        <div class="event-meta-row">
                            <!-- Location icon -->
                            <svg class="event-meta-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M8 14s-5-4.5-5-8a5 5 0 0 1 10 0c0 3.5-5 8-5 8z"/>
                                <circle cx="8" cy="6" r="1.5"/>
                            </svg>
                            <?= htmlspecialchars($event['venue']) ?>
                        </div>
                        <div class="event-meta-row">
                            <!-- People icon -->
                            <svg class="event-meta-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="6" cy="5" r="2.5"/>
                                <path d="M1 13c0-2.5 2-4 5-4s5 1.5 5 4"/>
                                <circle cx="12" cy="5" r="2"/>
                                <path d="M12 9c2 0 3.5 1 3.5 3"/>
                            </svg>
                            Limited to <?= (int)$event['capacity'] ?> seats
                        </div>
                    </div>

                    <a href="<?= SITE_URL ?>/register.php?event=<?= (int)$event['id'] ?>"
                       class="btn btn-primary btn-sm">
                        Register for this event
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════════════
     WHY JOIN  (workshops section)
     ════════════════════════════════════════════════════════════════════════════ -->
<section class="section section-alt" id="workshops">
    <div class="section-container">
        <div class="section-header">
            <div class="section-eyebrow">Workshops</div>
            <h2 class="section-title">Why Join Our Sessions?</h2>
            <p class="section-subtitle">
                Every workshop is designed by students, for students — practical, focused, and beginner-friendly.
            </p>
        </div>

        <div class="features-grid">
            <?php
            $features = [
                ['Hands-on Learning',  'Every workshop includes exercises and real projects you can add to your portfolio.'],
                ['Peer Community',     'Learn alongside fellow students. Collaborate, ask questions, and grow together.'],
                ['Participation Certificate', 'Receive a digital certificate of participation for each event you attend.'],
                ['Free Resources',     'All workshop materials, slides, and code samples are shared with participants.'],
            ];
            foreach ($features as [$title, $desc]):
            ?>
            <div class="feature-card">
                <div class="feature-card-dot"></div>
                <h3><?= $title ?></h3>
                <p><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════════════
     ABOUT
     ════════════════════════════════════════════════════════════════════════════ -->
<section class="section" id="about">
    <div class="section-container">
        <div class="about-grid">

            <div>
                <div class="section-eyebrow">About Us</div>
                <h2 class="section-title mt-0">A Community Built by Students</h2>

                <p class="about-prose">
                    CampusConnect is a student-run technical community that organises events, workshops,
                    and collaborative sessions throughout the academic year. Our goal is to make learning
                    accessible and engaging for everyone, regardless of background or experience.
                </p>
                <p class="about-prose">
                    Whether you are a first-year student curious about programming or a senior looking to
                    sharpen your skills, there is a place for you here.
                </p>

                <div class="about-features">
                    <?php
                    $points = [
                        ['Skill-focused events',  'Each event targets a specific skill area to maximise learning impact.'],
                        ['Open to all branches',  'Any student from any department is welcome to attend and contribute.'],
                        ['Beginner-friendly',     'All sessions start from the basics — no prior experience required.'],
                    ];
                    foreach ($points as [$title, $desc]):
                    ?>
                    <div class="about-feature">
                        <div class="about-feature-bullet"></div>
                        <div class="about-feature-text">
                            <h4><?= $title ?></h4>
                            <p><?= $desc ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="about-stats-panel">
                <div class="about-stat">
                    <div class="about-stat-num"><?= $total_participants ?>+</div>
                    <div class="about-stat-label">Students</div>
                </div>
                <div class="about-stat">
                    <div class="about-stat-num"><?= $total_events ?></div>
                    <div class="about-stat-label">Events</div>
                </div>
                <div class="about-stat">
                    <div class="about-stat-num">4</div>
                    <div class="about-stat-label">Departments</div>
                </div>
                <div class="about-stat">
                    <div class="about-stat-num">Free</div>
                    <div class="about-stat-label">To Attend</div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════════════
     CTA STRIP
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="cta-strip">
    <h2 class="cta-strip-title">Ready to join?</h2>
    <p class="cta-strip-sub">
        Registration is free and takes less than a minute.
        Join hundreds of students learning and building together.
    </p>
    <a href="<?= SITE_URL ?>/register.php" class="btn btn-white btn-lg">Create Your Account</a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
