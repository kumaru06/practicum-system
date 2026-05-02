USE practicum_system;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS password_changed TINYINT(1) NOT NULL DEFAULT 1 AFTER is_active;

UPDATE users SET password_changed = 0 WHERE role = 'student';
UPDATE users SET password_changed = 1 WHERE role <> 'student';
