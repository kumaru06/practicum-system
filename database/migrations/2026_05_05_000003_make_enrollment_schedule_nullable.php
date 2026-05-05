<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE ojt_enrollments MODIFY start_date DATE NULL');
        DB::statement('ALTER TABLE ojt_enrollments MODIFY end_date DATE NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE ojt_enrollments SET start_date = COALESCE(start_date, official_start_date, term_start_date, CURRENT_DATE()), end_date = COALESCE(end_date, projected_end_date, term_end_date, CURRENT_DATE()) WHERE start_date IS NULL OR end_date IS NULL");
        DB::statement('ALTER TABLE ojt_enrollments MODIFY start_date DATE NOT NULL');
        DB::statement('ALTER TABLE ojt_enrollments MODIFY end_date DATE NOT NULL');
    }
};