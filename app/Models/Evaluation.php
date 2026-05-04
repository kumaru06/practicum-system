<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    public $timestamps = false;
    protected $fillable = ['enrollment_id', 'company_id', 'rating', 'comments', 'submitted_at'];
    protected $casts = ['rating' => 'integer', 'submitted_at' => 'datetime'];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(OjtEnrollment::class, 'enrollment_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(PartnerCompany::class, 'company_id');
    }
}
