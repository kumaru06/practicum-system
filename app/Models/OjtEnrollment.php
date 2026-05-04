<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OjtEnrollment extends Model
{
    protected $table = 'ojt_enrollments';
    public $timestamps = false;
    protected $fillable = [
        'student_id', 'company_id', 'academic_term', 'term_start_date', 'term_end_date', 'start_date', 'end_date',
        'required_hours', 'status', 'predeployment_status', 'endorsement_file', 'forwarded_at', 'accepted_at',
        'orientation_datetime', 'orientation_notes', 'official_start_date', 'projected_end_date',
    ];
    protected $casts = [
        'term_start_date' => 'date', 'term_end_date' => 'date', 'start_date' => 'date', 'end_date' => 'date',
        'forwarded_at' => 'datetime', 'accepted_at' => 'datetime', 'orientation_datetime' => 'datetime',
        'official_start_date' => 'date', 'projected_end_date' => 'date', 'created_at' => 'datetime',
        'required_hours' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(PartnerCompany::class, 'company_id');
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class, 'enrollment_id');
    }
}
