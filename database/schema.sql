CREATE DATABASE IF NOT EXISTS practicum_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practicum_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS weekly_reports;
DROP TABLE IF EXISTS daily_time_records;
DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS ojt_enrollments;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS coordinators;
DROP TABLE IF EXISTS partner_companies;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','coordinator','student','partner') NOT NULL,
  created_by INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  password_changed TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE coordinators (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  department VARCHAR(120) DEFAULT 'OJT Department',
  CONSTRAINT fk_coordinators_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE partner_companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  name VARCHAR(190) NOT NULL,
  address TEXT NOT NULL,
  contact_person VARCHAR(150) NOT NULL,
  contact_email VARCHAR(190) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  student_no VARCHAR(60) NOT NULL UNIQUE,
  course VARCHAR(120) NOT NULL,
  year_level VARCHAR(60) NOT NULL,
  cor_file VARCHAR(255) NOT NULL,
  coordinator_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_students_coord FOREIGN KEY (coordinator_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ojt_enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL UNIQUE,
  company_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  required_hours INT NOT NULL,
  status ENUM('pending','active','completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_enroll_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_enroll_company FOREIGN KEY (company_id) REFERENCES partner_companies(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE daily_time_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  work_date DATE NOT NULL,
  time_in TIME NOT NULL,
  time_out TIME NOT NULL,
  hours DECIMAL(6,2) NOT NULL,
  tasks_done TEXT NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dtr_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE weekly_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  week_no INT NOT NULL,
  report_text TEXT NULL,
  file_path VARCHAR(255) NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reports_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE evaluations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT NOT NULL UNIQUE,
  company_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comments TEXT NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_enrollment FOREIGN KEY (enrollment_id) REFERENCES ojt_enrollments(id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_company FOREIGN KEY (company_id) REFERENCES partner_companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE email_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_email VARCHAR(190) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  type VARCHAR(80) NOT NULL,
  sent_at DATETIME NOT NULL,
  status ENUM('sent','failed') NOT NULL,
  error_message TEXT NULL
) ENGINE=InnoDB;
