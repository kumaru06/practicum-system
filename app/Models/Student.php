<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id', 'student_no', 'program_id', 'course', 'year_level', 'section', 'cor_file', 'photo_file',
        'address', 'contact_number', 'emergency_contact_name', 'emergency_contact_number', 'guardian_name',
        'guardian_contact', 'profile_completed', 'coordinator_id',
    ];
    protected $casts = ['profile_completed' => 'boolean', 'created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(OjtEnrollment::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class);
    }

    public function dailyTimeRecords(): HasMany
    {
        return $this->hasMany(DailyTimeRecord::class);
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class);
    }
}
