<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ojt_enrollments MODIFY predeployment_status ENUM('not_submitted','submitted','approved','needs_revision','forwarded','accepted','orientation_scheduled','orientation_completed') NOT NULL DEFAULT 'not_submitted'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ojt_enrollments MODIFY predeployment_status ENUM('not_submitted','submitted','approved','forwarded','accepted','orientation_scheduled','orientation_completed') NOT NULL DEFAULT 'not_submitted'");
    }
};