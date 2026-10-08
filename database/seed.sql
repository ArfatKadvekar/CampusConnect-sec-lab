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
