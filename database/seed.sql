-- ─────────────────────────────────────────────────────────────────────────────
--  CampusConnect Security Lab — Seed Data  (entirely fictional)
-- ─────────────────────────────────────────────────────────────────────────────

USE campusconnect_lab;

-- ─────────────────────────────────────────────────────────────────────────────
--  Fictional events
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO events (title, slug, description, event_date, event_time, venue, capacity) VALUES
(
    'Web Development Workshop',
    'web-dev-workshop',
    'A hands-on introduction to modern web development. Participants will learn HTML, CSS, and JavaScript basics and build a simple interactive webpage from scratch. No prior experience needed.',
    '2026-11-10', '10:00:00', 'Seminar Hall A', 80
),
(
    'Introduction to Linux',
    'intro-linux',
    'Get comfortable with the Linux command line. This session covers filesystem navigation, basic shell commands, file permissions, and writing simple bash scripts.',
    '2026-11-18', '14:00:00', 'Computer Lab 3', 40
),
(
    'Beginner Cybersecurity Session',
    'beginner-cybersecurity',
    'An entry-level overview of cybersecurity concepts including the CIA triad, common attack types, password security, and safe browsing habits. Ideal for students curious about information security.',
    '2026-12-02', '11:00:00', 'Seminar Hall B', 60
),
(
    'Competitive Programming Bootcamp',
    'cp-bootcamp',
    'Two-day intensive bootcamp covering problem-solving strategies, data structures, and algorithm design patterns. Practice problems and mock contests included.',
    '2026-12-15', '09:00:00', 'Computer Lab 1', 50
);

-- ─────────────────────────────────────────────────────────────────────────────
--  Fictional participants  (password = "demo1234" for all, bcrypt-hashed)
--  These are entirely fictional names and email addresses.
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO participants (name, email, password, branch, year) VALUES
('Rohan Mehta',    'rohan.mehta@example-college.edu',    '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Computer Science', 1),
('Priya Sharma',   'priya.sharma@example-college.edu',   '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Information Technology', 2),
('Aditya Kumar',   'aditya.kumar@example-college.edu',   '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Electronics',      1),
('Sneha Patil',    'sneha.patil@example-college.edu',    '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Computer Science', 3),
('Vikram Singh',   'vikram.singh@example-college.edu',   '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Mechanical',       2),
('Ananya Rao',     'ananya.rao@example-college.edu',     '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Information Technology', 1),
('Karan Joshi',    'karan.joshi@example-college.edu',    '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Computer Science', 4),
('Divya Nair',     'divya.nair@example-college.edu',     '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Civil',            2),
('Siddharth Gupta','siddharth.gupta@example-college.edu','$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Computer Science', 1),
('Tanvi Desai',    'tanvi.desai@example-college.edu',    '$2y$10$eImiTXuWVxfM37uY9cTvae8nrQ9Ln2P3l1A4O5G7H8I9J0K1L2M3N', 'Electronics',      3);

-- ─────────────────────────────────────────────────────────────────────────────
--  Fictional registrations
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO registrations (participant_id, event_id) VALUES
(1,1),(1,2),(1,3),
(2,1),(2,4),
(3,2),(3,3),
(4,1),(4,3),(4,4),
(5,2),
(6,1),(6,2),(6,3),(6,4),
(7,4),
(8,1),(8,2),
(9,3),(9,4),
(10,1);

-- ─────────────────────────────────────────────────────────────────────────────
--  Fictional Demo Accounts for Password Security Lab
--  Disposable synthetic accounts (A, B, C, D)
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO demo_accounts 
    (account_code, username, display_name, category, password_length, is_predictable, target_password, password_hash, scenario_mode, is_locked, failed_attempts, locked_until, login_demonstrated, mfa_enabled, mfa_secret)
VALUES
(
    'A',
    'demo_student_01',
    'Student Account A',
    'Predictable (Campus-Themed)',
    10,
    1,
    'campus2026',
    '$2y$10$wU0M7Z8yD9gZ5rP2sX9J7eF9A1B2C3D4E5F6G7H8I9J0K1L2M3N4O',
    'insecure',
    0,
    0,
    NULL,
    0,
    0,
    'DEMO-OTP-849201'
),
(
    'B',
    'demo_student_02',
    'Student Account B',
    'Modified Pattern (Capital + Symbol + Year)',
    11,
    1,
    'Campus@2026',
    '$2y$10$tV1N8A9zE0hA6sQ3tY0K8fG0B2C3D4E5F6G7H8I9J0K1L2M3N4O5P',
    'insecure',
    0,
    0,
    NULL,
    0,
    0,
    'DEMO-OTP-739104'
),
(
    'C',
    'demo_student_03',
    'Student Account C',
    'Random Generated String (High Entropy)',
    16,
    0,
    'rN7#kP9!wB2$xT5&',
    '$2y$10$uW2O9B0aF1iB7tR4uZ1L9gH1C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q',
    'protected',
    0,
    0,
    NULL,
    0,
    1,
    'DEMO-OTP-519283'
),
(
    'D',
    'demo_student_04',
    'Student Account D',
    'Diceware Multi-Word Passphrase',
    24,
    0,
    'falcon-river-blue-matrix',
    '$2y$10$vX3P0C1bG2jC8uS5va2M0hI2D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R',
    'protected',
    0,
    0,
    NULL,
    0,
    1,
    'DEMO-OTP-629405'
)
ON DUPLICATE KEY UPDATE
    display_name = VALUES(display_name),
    category = VALUES(category),
    password_length = VALUES(password_length),
    is_predictable = VALUES(is_predictable),
    target_password = VALUES(target_password),
    password_hash = VALUES(password_hash),
    mfa_secret = VALUES(mfa_secret);

