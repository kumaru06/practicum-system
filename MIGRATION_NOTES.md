# Laravel Migration Notes

This folder is the Laravel migration of the native PHP practicum system.

## What has been migrated

- Laravel 12 project scaffold in `laravel-app/`
- Existing public assets copied to `public/assets/`
- Existing uploaded files copied to `public/uploads/`
- Existing PHP views copied as Blade-compatible views under `resources/views/legacy/`
- Current URL style preserved for now:
  - `/auth.php`
  - `/logout.php`
  - `/index.php?r=admin`
  - `/index.php?r=coordinator_manage`
- Parent native PHP model classmapping has been removed.
- Laravel-native model classes now exist in `app/Models/` for users, coordinators, programs, partner companies, students, requirements, enrollments, reports, evaluations, email logs, and notifications.
- Remaining compatibility workflow methods are now inside the Laravel app at `app/Support/laravel_compat_models.php` and use Laravel DB/Mail/View facades instead of the parent native PHP folders.
- Existing database schema converted to a Laravel migration.
- Default admin/program seed data converted to a Laravel seeder.

## Current migration phase

This is now a self-contained Laravel port. It boots through Laravel routes/controllers and no longer autoloads the parent native `models/`, parent `config/mail.php`, or parent `vendor/` folder.

As of May 4, 2026, the existing seeded admin login was tested successfully through Laravel using:

```text
admin@ama.edu.ph / Admin@123
```

The Laravel app handles the main POST actions, including:

- Admin partner/company creation
- Coordinator student enrollment
- Coordinator requirement review
- Coordinator student password reset
- Coordinator deployment forwarding
- Partner deployment acceptance
- Partner orientation email sending
- Partner orientation scheduling
- Partner orientation completion
- Partner final evaluation submission
- Student profile saving
- Student weekly report submission

## Run locally

```powershell
cd laravel-app
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000/auth.php
```

## Database

The `.env` file is configured for the existing XAMPP MySQL database:

```text
DB_DATABASE=practicum_system
DB_USERNAME=root
DB_PASSWORD=
```

If you need to rebuild a fresh database, run:

```powershell
php artisan migrate:fresh --seed
```

Warning: `migrate:fresh` deletes existing table data.

## Current validation

After removing parent native PHP dependencies, the admin login and admin pages were tested successfully through Laravel.

Validated routes:

- `admin`
- `admin_users`
- `admin_coordinators`
- `admin_partners`
- `admin_programs`
- `admin_email_logs`
- `admin_evaluations`

## Next recommended cleanup phase

1. Replace the remaining compatibility workflow classes with separate Laravel service/controller classes.
2. Replace the action dispatcher with separate Laravel resource controllers.
3. Convert `csrf_token` hidden fields to Laravel `_token` fields.
4. Move uploads fully to Laravel Storage.
5. Add role middleware for admin/coordinator/student/partner.
