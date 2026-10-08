<?php
/**
 * CampusConnect Security Lab — Participant Logout
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

participant_logout();
header('Location: ' . SITE_URL . '/login.php');
exit;
