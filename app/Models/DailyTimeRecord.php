<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTimeRecord extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'work_date', 'time_in', 'time_out', 'hours', 'tasks_done', 'submitted_at'];
    protected $casts = ['work_date' => 'date', 'hours' => 'decimal:2', 'submitted_at' => 'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
