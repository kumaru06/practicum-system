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

        $existing = DB::table('users')->where('email', 'admin@ama.edu.ph')->first();

        if ($existing) {
            DB::table('users')->where('email', 'admin@ama.edu.ph')->update([
                'password_hash'    => $hash,
                'is_active'        => 1,
                'password_changed' => 1,
                'role'             => 'admin',
            ]);
            $this->info('Admin user updated: admin@ama.edu.ph / Admin@123');
        } else {
            DB::table('users')->insert([
                'name'             => 'System Administrator',
                'email'            => 'admin@ama.edu.ph',
                'password_hash'    => $hash,
                'role'             => 'admin',
                'created_by'       => null,
                'is_active'        => 1,
                'password_changed' => 1,
            ]);
            $this->info('Admin user created: admin@ama.edu.ph / Admin@123');
        }

        return 0;
    }
}

