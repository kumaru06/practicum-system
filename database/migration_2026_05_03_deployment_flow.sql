USE practicum_system;

CREATE TABLE IF NOT EXISTS programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(190) NOT NULL,
  required_hours INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO programs (code, name, required_hours)
VALUES
  ('BSIT', 'Bachelor of Science in Information Technology', 486),
  ('BSBA', 'Bachelor of Science in Business Administration', 600),
  ('BSCS', 'Bachelor of Science in Computer Science', 120),
  ('BSCOE', 'Bachelor of Science in Computer Engineering', 240)
ON DUPLICATE KEY UPDATE name = VALUES(name), required_hours = VALUES(required_hours), is_active = 1;

CREATE TABLE IF NOT EXISTS company_programs (
  company_id INT NOT NULL,
  program_id INT NOT NULL,
  PRIMARY KEY (company_id, program_id),
  CONSTRAINT fk_company_program_company FOREIGN KEY (company_id) REFERENCES partner_companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_company_program_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE partner_companies
  ADD COLUMN IF NOT EXISTS contact_number VARCHAR(60) NULL AFTER contact_email;

ALTER TABLE students
  ADD COLUMN IF NOT EXISTS program_id INT NULL AFTER student_no,
  ADD COLUMN IF NOT EXISTS photo_file VARCHAR(255) NULL AFTER cor_file,
  ADD COLUMN IF NOT EXISTS address TEXT NULL AFTER photo_file,
  ADD COLUMN IF NOT EXISTS contact_number VARCHAR(60) NULL AFTER address,
  ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(150) NULL AFTER contact_number,
  ADD COLUMN IF NOT EXISTS emergency_contact_number VARCHAR(60) NULL AFTER emergency_contact_name,
  ADD COLUMN IF NOT EXISTS guardian_name VARCHAR(150) NULL AFTER emergency_contact_number,
  ADD COLUMN IF NOT EXISTS guardian_contact VARCHAR(60) NULL AFTER guardian_name,
  ADD COLUMN IF NOT EXISTS section VARCHAR(80) NULL AFTER year_level,
  ADD COLUMN IF NOT EXISTS profile_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER section;

ALTER TABLE ojt_enrollments
  ADD COLUMN IF NOT EXISTS academic_term VARCHAR(40) NULL AFTER company_id,
  ADD COLUMN IF NOT EXISTS term_start_date DATE NULL AFTER academic_term,
  ADD COLUMN IF NOT EXISTS term_end_date DATE NULL AFTER term_start_date,
  ADD COLUMN IF NOT EXISTS predeployment_status ENUM('not_submitted','submitted','approved','forwarded','accepted','orientation_scheduled','orientation_completed') NOT NULL DEFAULT 'not_submitted' AFTER status,
  ADD COLUMN IF NOT EXISTS endorsement_file VARCHAR(255) NULL AFTER predeployment_status,
  ADD COLUMN IF NOT EXISTS forwarded_at DATETIME NULL AFTER endorsement_file,
  ADD COLUMN IF NOT EXISTS accepted_at DATETIME NULL AFTER forwarded_at,
  ADD COLUMN IF NOT EXISTS orientation_datetime DATETIME NULL AFTER accepted_at,
  ADD COLUMN IF NOT EXISTS orientation_notes TEXT NULL AFTER orientation_datetime,
  ADD COLUMN IF NOT EXISTS official_start_date DATE NULL AFTER orientation_notes,
  ADD COLUMN IF NOT EXISTS projected_end_date DATE NULL AFTER official_start_date;

CREATE TABLE IF NOT EXISTS student_requirements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  requirement_key VARCHAR(80) NOT NULL,
  requirement_name VARCHAR(150) NOT NULL,
  file_path VARCHAR(255) NULL,
  status ENUM('pending','uploaded','approved','rejected') NOT NULL DEFAULT 'pending',
  notes TEXT NULL,
  uploaded_at DATETIME NULL,
  reviewed_at DATETIME NULL,
  UNIQUE KEY uq_student_requirement (student_id, requirement_key),
  CONSTRAINT fk_requirements_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

UPDATE students s
LEFT JOIN programs p ON p.name = s.course OR p.code = s.course
SET s.program_id = p.id
WHERE s.program_id IS NULL AND p.id IS NOT NULL;
