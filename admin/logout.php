<?php
/**
 * CampusConnect Security Lab — Admin Logout
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

admin_logout();
header('Location: ' . SITE_URL . '/admin/index.php');
exit;
