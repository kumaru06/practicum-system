<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'week_no', 'report_text', 'file_path', 'submitted_at'];
    protected $casts = ['week_no' => 'integer', 'submitted_at' => 'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
