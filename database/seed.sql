USE practicum_system;

-- ─── Users ────────────────────────────────────────────────────────────────────
-- password for all accounts: password123
INSERT INTO users (id, name, email, password_hash, role, created_by, is_active, password_changed) VALUES
(1,  'System Administrator',     'admin@ama.edu.ph',            '$2y$10$9CHq.Pz4X5vuhbXpv5DE6O6FOtawSqC7eoj/kXj6UBJ3jgHH8Rp/O', 'admin',       NULL, 1, 1),
-- coordinators
(2,  'Maria Santos',             'coordinator@ama.edu.ph',      '$2y$10$/wq3b0xi.SUjW2YJOnU0/eioo0dj6s.jeddmJ0E1mHQSzxs02O4DC', 'coordinator', 1,    1, 1),
(3,  'Kram Dreyan',              'coordinator2@ama.edu.ph',     '$2y$10$/wq3b0xi.SUjW2YJOnU0/eioo0dj6s.jeddmJ0E1mHQSzxs02O4DC', 'coordinator', 1,    1, 1),
-- partners
(4,  'TechBridge Solutions Inc.','partner@example.com',         '$2y$10$tgQzyp7WJHjq/eH1Q0tW0OyT9IXsTDv6LBMSFHfb95uCcp1vz55IC', 'partner',     1,    1, 1),
(5,  'InnoSoft PH',              'innosoft@example.com',        '$2y$10$tgQzyp7WJHjq/eH1Q0tW0OyT9IXsTDv6LBMSFHfb95uCcp1vz55IC', 'partner',     1,    1, 1),
(6,  'DataCore Asia',            'datacore@example.com',        '$2y$10$tgQzyp7WJHjq/eH1Q0tW0OyT9IXsTDv6LBMSFHfb95uCcp1vz55IC', 'partner',     1,    1, 1),
(7,  'NexGen Systems',           'nexgen@example.com',          '$2y$10$tgQzyp7WJHjq/eH1Q0tW0OyT9IXsTDv6LBMSFHfb95uCcp1vz55IC', 'partner',     1,    1, 1),
-- students (coordinator 2)
(8,  'Juan Dela Cruz',           'juan.student@example.com',    '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     2,    1, 1),
(9,  'Ana Reyes',                'ana.student@example.com',     '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     2,    1, 1),
(10, 'Carlo Bautista',           'carlo.student@example.com',   '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     2,    1, 1),
(11, 'Lena Villanueva',          'lena.student@example.com',    '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     2,    1, 1),
(12, 'Marco Ramos',              'marco.student@example.com',   '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     2,    1, 1),
-- students (coordinator 3)
(13, 'Sofia Mendoza',            'sofia.student@example.com',   '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     3,    1, 1),
(14, 'Ryan Torres',              'ryan.student@example.com',    '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     3,    1, 1),
(15, 'Patricia Cruz',            'patricia.student@example.com','$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     3,    1, 1),
(16, 'Miguel Aquino',            'miguel.student@example.com',  '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     3,    1, 1),
(17, 'Jasmine Lim',              'jasmine.student@example.com', '$2y$10$yWb5/W1n2o2ikAfB0QAc1OXjFeedpq21/LMSta7xk0o3RwDD5.uvG', 'student',     3,    1, 1);

-- ─── Coordinators ─────────────────────────────────────────────────────────────
INSERT INTO coordinators (user_id, department) VALUES
(2, 'OJT Department'),
(3, 'OJT Department');

-- ─── Partner Companies ────────────────────────────────────────────────────────
INSERT INTO partner_companies (id, user_id, name, address, contact_person, contact_email) VALUES
(1, 4, 'TechBridge Solutions Inc.', 'Makati City, Metro Manila',    'Carlo Mendoza',  'partner@example.com'),
(2, 5, 'InnoSoft PH',               'BGC, Taguig City',             'Ella Pascual',   'innosoft@example.com'),
(3, 6, 'DataCore Asia',             'Ortigas Center, Pasig City',   'Ben Huang',      'datacore@example.com'),
(4, 7, 'NexGen Systems',            'Cebu IT Park, Cebu City',      'Rina Castro',    'nexgen@example.com');

-- ─── Students ─────────────────────────────────────────────────────────────────
INSERT INTO students (id, user_id, student_no, course, year_level, cor_file, coordinator_id) VALUES
(1,  8,  'AMA-2026-0001', 'BS Information Technology', '4th Year', 'uploads/cor/placeholder.pdf', 2),
(2,  9,  'AMA-2026-0002', 'BS Computer Science',       '4th Year', 'uploads/cor/placeholder.pdf', 2),
(3,  10, 'AMA-2026-0003', 'BS Information Technology', '4th Year', 'uploads/cor/placeholder.pdf', 2),
(4,  11, 'AMA-2026-0004', 'BSIT',                      '3rd Year', 'uploads/cor/placeholder.pdf', 2),
(5,  12, 'AMA-2026-0005', 'BS Computer Science',       '4th Year', 'uploads/cor/placeholder.pdf', 2),
(6,  13, 'AMA-2026-0006', 'BSIT',                      '4th Year', 'uploads/cor/placeholder.pdf', 3),
(7,  14, 'AMA-2026-0007', 'BS Information Technology', '3rd Year', 'uploads/cor/placeholder.pdf', 3),
(8,  15, 'AMA-2026-0008', 'BS Computer Science',       '4th Year', 'uploads/cor/placeholder.pdf', 3),
(9,  16, 'AMA-2026-0009', 'BSIT',                      '4th Year', 'uploads/cor/placeholder.pdf', 3),
(10, 17, 'AMA-2026-0010', 'BS Computer Science',       '3rd Year', 'uploads/cor/placeholder.pdf', 3);

-- ─── OJT Enrollments (spread across months, mixed statuses) ──────────────────
INSERT INTO ojt_enrollments (student_id, company_id, start_date, end_date, required_hours, status, created_at) VALUES
(1,  1, '2025-11-04', '2026-02-04', 486, 'completed', '2025-11-04 08:00:00'),
(2,  2, '2025-11-10', '2026-02-10', 486, 'completed', '2025-11-10 08:00:00'),
(3,  1, '2025-12-02', '2026-03-02', 486, 'completed', '2025-12-02 08:00:00'),
(4,  3, '2026-01-06', '2026-04-06', 486, 'completed', '2026-01-06 08:00:00'),
(5,  2, '2026-01-13', '2026-04-13', 486, 'completed', '2026-01-13 08:00:00'),
(6,  4, '2026-02-03', '2026-05-03', 486, 'completed', '2026-02-03 08:00:00'),
(7,  3, '2026-02-10', '2026-05-10', 486, 'active',    '2026-02-10 08:00:00'),
(8,  1, '2026-03-03', '2026-06-03', 486, 'active',    '2026-03-03 08:00:00'),
(9,  4, '2026-04-07', '2026-07-07', 486, 'active',    '2026-04-07 08:00:00'),
(10, 2, '2026-05-05', '2026-08-05', 486, 'active',    '2026-05-05 08:00:00');

-- ─── Daily Time Records ───────────────────────────────────────────────────────
INSERT INTO daily_time_records (student_id, work_date, time_in, time_out, hours, tasks_done) VALUES
(1, '2026-01-05', '08:00:00', '17:00:00', 8.00, 'System analysis'),
(1, '2026-01-06', '08:00:00', '17:00:00', 8.00, 'Database design'),
(2, '2026-01-07', '08:00:00', '17:00:00', 8.00, 'Frontend development'),
(3, '2026-02-03', '08:00:00', '17:00:00', 8.00, 'API integration'),
(4, '2026-02-10', '08:00:00', '17:00:00', 8.00, 'QA testing'),
(5, '2026-03-04', '08:00:00', '17:00:00', 8.00, 'Deployment support'),
(6, '2026-03-11', '08:00:00', '17:00:00', 8.00, 'Network setup'),
(7, '2026-04-02', '08:00:00', '17:00:00', 8.00, 'Documentation'),
(8, '2026-04-09', '08:00:00', '17:00:00', 8.00, 'Bug fixing'),
(9, '2026-05-06', '08:00:00', '17:00:00', 8.00, 'Orientation'),
(10,'2026-05-07', '08:00:00', '17:00:00', 8.00, 'Onboarding tasks');

-- ─── Evaluations (for completed enrollments) ──────────────────────────────────
INSERT INTO evaluations (enrollment_id, company_id, rating, comments, submitted_at) VALUES
(1, 1, 5, 'Excellent performance, very proactive.',        '2026-02-05 10:00:00'),
(2, 2, 4, 'Good communication and technical skills.',      '2026-02-11 10:00:00'),
(3, 1, 5, 'Outstanding. Highly recommended.',              '2026-03-03 10:00:00'),
(4, 3, 4, 'Solid work ethic, met all deadlines.',          '2026-04-07 10:00:00'),
(5, 2, 3, 'Average performance, needs improvement.',       '2026-04-14 10:00:00'),
(6, 4, 5, 'Exceptional student, exceeded expectations.',   '2026-05-04 10:00:00');
