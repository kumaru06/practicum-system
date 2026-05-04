<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PracticumSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'System Administrator',
                'email' => 'admin@ama.edu.ph',
                'password_hash' => '$2y$10$9CHq.Pz4X5vuhbXpv5DE6O6FOtawSqC7eoj/kXj6UBJ3jgHH8Rp/O',
                'role' => 'admin',
                'created_by' => null,
                'is_active' => 1,
                'password_changed' => 1,
            ]
        );

        foreach ([
            ['id' => 1, 'code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology', 'required_hours' => 486, 'is_active' => 1],
            ['id' => 2, 'code' => 'BSBA', 'name' => 'Bachelor of Science in Business Administration', 'required_hours' => 600, 'is_active' => 1],
            ['id' => 3, 'code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science', 'required_hours' => 120, 'is_active' => 1],
            ['id' => 4, 'code' => 'BSCOE', 'name' => 'Bachelor of Science in Computer Engineering', 'required_hours' => 240, 'is_active' => 1],
        ] as $program) {
            DB::table('programs')->updateOrInsert(['id' => $program['id']], $program);
        }
    }
}
