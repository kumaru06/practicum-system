# AMA Practicum / OJT Management System

Laravel-native practicum management system for AMA Computer College. It supports admin, OJT coordinator, student, and partner company workflows.

## Main Features

- Admin dashboard with enrollment statistics, charts, programs/courses, user status controls, evaluations, email logs, and company management.
- OJT coordinator dashboard with student creation, student enrollment, requirement review, deployment forwarding, student password reset, and evaluations.
- Student portal with forced temporary password change, profile completion, pre-deployment document uploads, daily time records, weekly narrative reports, and progress tracking.
- Partner company portal with forced temporary password change, deployment acceptance, orientation email/scheduling/completion, DTR viewing, and final evaluation submission.
- SMTP email delivery with an audit log for account credentials, student enrollment, company deployment, forwarded documents, orientation notices, password resets, and test messages.
- Notification dropdown with unread counts and mark-all-as-read action.

## Local URL

The app is configured for XAMPP subfolder deployment:

`http://localhost/practicum-system`

## Environment Notes

- Database: MySQL database named `practicum_system`.
- Session/cache: file-based storage is used for XAMPP compatibility.
- Mail: SMTP is configured in `.env`. If email is not received, check the Email Logs screen first.
- Public uploads are stored under `public/uploads`.

## Useful Commands

Run from the project root:

- `composer install`
- `php artisan migrate`
- `php artisan route:list`
- `php artisan view:clear`
- `php artisan cache:clear`

Do not run destructive reset commands such as `migrate:fresh` on a live database unless you intentionally want to delete current records.

## Default Access

Use the existing seeded/admin account in the database. Temporary accounts for coordinators, students, and companies must change their password on first login.

## Troubleshooting

- If CSS or JavaScript looks old, hard-refresh the browser with `Ctrl+F5`.
- If a user cannot enter a portal, confirm the account is active and whether `password_changed` is set to `0`; non-admin users are redirected to the password-change page until updated.
- If company or coordinator emails are missing, use the resend/reset buttons and inspect Email Logs for the SMTP status.
- If uploads fail, verify the file is PDF/JPG/PNG and below the configured size limit.
