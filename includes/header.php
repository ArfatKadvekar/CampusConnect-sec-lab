<?php
/**
 * CampusConnect Security Lab — Shared HTML header
 *
 * @var string $page_title   Override the <title> tag
 * @var string $active_nav   Which nav link to highlight
 */
if (!isset($page_title))  $page_title  = SITE_NAME . ' — Events & Workshops';
if (!isset($active_nav))  $active_nav  = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CampusConnect — Your student community hub for technical events, workshops, and learning sessions.">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- ── Navigation ─────────────────────────────────────────────────────────── -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?= SITE_URL ?>/" class="nav-brand">
            <div class="brand-icon">CC</div>
            <span class="brand-name"><?= SITE_NAME ?></span>
        </a>

        <ul class="nav-links" id="navLinks">
            <li><a href="<?= SITE_URL ?>/"              class="nav-link <?= $active_nav === 'home'      ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/#events"       class="nav-link <?= $active_nav === 'events'    ? 'active' : '' ?>">Events</a></li>
            <li><a href="<?= SITE_URL ?>/#workshops"    class="nav-link <?= $active_nav === 'workshops'  ? 'active' : '' ?>">Workshops</a></li>
            <li><a href="<?= SITE_URL ?>/#about"        class="nav-link <?= $active_nav === 'about'     ? 'active' : '' ?>">About</a></li>
            <?php if (is_participant_logged_in()): ?>
                <li><span class="nav-greeting">Hi, <?= htmlspecialchars($_SESSION['participant_name']) ?>!</span></li>
                <li><a href="<?= SITE_URL ?>/logout.php" class="nav-btn btn-outline">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/login.php" class="nav-btn <?= $active_nav === 'login' ? 'active' : '' ?>">Login</a></li>
            <?php endif; ?>
        </ul>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
