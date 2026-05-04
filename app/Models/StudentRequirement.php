<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRequirement extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'requirement_key', 'requirement_name', 'file_path', 'status', 'notes', 'uploaded_at', 'reviewed_at'];
    protected $casts = ['uploaded_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
