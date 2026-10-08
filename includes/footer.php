<?php
/**
 * CampusConnect Security Lab — Shared HTML footer
 */
?>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="brand-icon brand-icon--sm">CC</div>
            <div>
                <p class="footer-name"><?= SITE_NAME ?></p>
                <p class="footer-tagline"><?= SITE_TAGLINE ?></p>
            </div>
        </div>

        <div class="footer-links">
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/#events">Events</a></li>
                    <li><a href="<?= SITE_URL ?>/register.php">Register</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Community</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/#about">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/login.php">Participant Login</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        <p class="footer-note">This is a fictional student organization created for educational purposes.</p>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
</body>
</html>
