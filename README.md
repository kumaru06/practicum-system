# AMA Computer College Practicum Management System

Pure PHP 8 + PDO MySQL OJT management system with four roles: Admin, OJT Coordinator, Student, and Partner Company.

## Setup

1. Start Apache and MySQL in XAMPP.
2. Import the database files in phpMyAdmin or MySQL CLI:
   - `database/schema.sql`
   - `database/seed.sql`
3. Confirm database credentials in `config/database.php`.
4. Configure real SMTP credentials in `config/mail.php`.
   - Gmail requires an App Password.
   - PHPMailer is installed through Composer in `vendor/`.
5. Open: `http://localhost/practicum-system/auth.php`

## Seed logins

- Admin: `admin@ama.edu.ph` / `Admin@123`
- Coordinator: `coordinator@ama.edu.ph` / `Coord@123`
- Partner: `partner@example.com` / `Company@123`
- Students: `juan.student@example.com` or `ana.student@example.com` / `Student@123`

## Notes

- COR uploads are validated as PDF/JPG/PNG and limited to 5MB.
- Weekly report uploads are validated as PDF and limited to 5MB.
- Email sending is real PHPMailer SMTP and every attempt is logged in `email_logs`.
- All POST forms use CSRF tokens.
