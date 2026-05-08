<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PracticumSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user — upsert by email so it always works regardless of id state
        $hash = password_hash('Admin@123', PASSWORD_BCRYPT);
        DB::statement("
            INSERT INTO users (id, name, email, password_hash, role, created_by, is_active, password_changed)
            VALUES (1, 'System Administrator', 'admin@ama.edu.ph', ?, 'admin', NULL, 1, 1)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                password_hash = VALUES(password_hash),
                role = VALUES(role),
                is_active = 1,
                password_changed = 1
        ", [$hash]);

        // Programs — upsert by id, ignore code conflicts
        foreach ([
            [1, 'BSIT',  'Bachelor of Science in Information Technology', 486,  1],
            [2, 'BSBA',  'Bachelor of Science in Business Administration', 600,  1],
            [3, 'BSCS',  'Bachelor of Science in Computer Science',        120,  1],
            [4, 'BSCOE', 'Bachelor of Science in Computer Engineering',    240,  1],
        ] as [$id, $code, $name, $hours, $active]) {
            DB::statement("
                INSERT INTO programs (id, code, name, required_hours, is_active)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    required_hours = VALUES(required_hours),
                    is_active = VALUES(is_active)
            ", [$id, $code, $name, $hours, $active]);
        }
    }
}
