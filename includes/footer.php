<?php
/**
 * CampusConnect Security Lab — Shared HTML footer
 */
?>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="site-footer">
    <div class="footer-main">

        <div class="footer-brand-block">
            <div class="footer-brand">
                <div class="brand-icon brand-icon--sm">CC</div>
                <span class="footer-brand-name"><?= SITE_NAME ?></span>
            </div>
            <p class="footer-desc">
                Student Events &amp; Technical Workshops.<br>
                CampusConnect is a student-run platform for organizing technical events and workshops on campus.
            </p>
        </div>

        <div class="footer-nav-group">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= SITE_URL ?>/">Home</a></li>
                <li><a href="<?= SITE_URL ?>/#events">Events</a></li>
                <li><a href="<?= SITE_URL ?>/#workshops">Workshops</a></li>
                <li><a href="<?= SITE_URL ?>/#about">About</a></li>
            </ul>
        </div>

        <div class="footer-nav-group">
            <h4>Account</h4>
            <ul>
                <li><a href="<?= SITE_URL ?>/register.php">Register</a></li>
                <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</span>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
</body>
</html>
