<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureAdminUser extends Command
{
    protected $signature   = 'admin:ensure';
    protected $description = 'Ensure the default admin user exists with the correct password.';

    public function handle(): int
    {
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);

        DB::statement("
            INSERT INTO users
                (id, name, email, password_hash, role, created_by, is_active, password_changed)
            VALUES
                (1, 'System Administrator', 'admin@ama.edu.ph', ?, 'admin', NULL, 1, 1)
            ON DUPLICATE KEY UPDATE
                name           = VALUES(name),
                password_hash  = VALUES(password_hash),
                role           = VALUES(role),
                is_active      = 1,
                password_changed = 1
        ", [$hash]);

        $this->info('Admin user ensured: admin@ama.edu.ph / Admin@123');
        return 0;
    }
}
