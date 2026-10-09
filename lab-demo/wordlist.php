<?php
/**
 * CampusConnect Security Lab — Wordlist Service
 *
 * Provides the synthetic demonstration candidate wordlist to the presentation runner.
 * Strictly restricted to localhost (127.0.0.1) for educational demonstrations.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/demo_helper.php';

enforce_localhost_only();

// Check instructor authorization
if (!is_instructor_authorized()) {
    http_response_code(403);
    echo json_encode(['error' => 'Instructor authorization required.']);
    exit;
}

$wordlist_file = get_demo_wordlist_path();

if (!file_exists($wordlist_file)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Demonstration wordlist file not found at: ' . $wordlist_file,
        'candidates' => []
    ]);
    exit;
}

// Read wordlist
$raw = file_get_contents($wordlist_file);
$lines = preg_split('/\r\n|\r|\n/', trim($raw));

// Filter out blank lines if any
$candidates = array_values(array_filter($lines, function($line) {
    return trim($line) !== '';
}));

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=3600');

echo json_encode([
    'success'          => true,
    'total_candidates' => count($candidates),
    'dataset_name'     => 'CampusConnect Educational Synthetic Candidate Wordlist',
    'candidates'       => $candidates,
], JSON_UNESCAPED_SLASHES);
