<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerCompany extends Model
{
    protected $table = 'partner_companies';
    public $timestamps = false;
    protected $fillable = ['user_id', 'name', 'address', 'contact_person', 'contact_email', 'contact_number'];
    protected $casts = ['created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'company_programs', 'company_id', 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(OjtEnrollment::class, 'company_id');
    }
}
