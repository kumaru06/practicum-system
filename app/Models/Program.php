<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    public $timestamps = false;
    protected $fillable = ['code', 'name', 'required_hours', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'required_hours' => 'integer', 'created_at' => 'datetime'];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(PartnerCompany::class, 'company_programs', 'program_id', 'company_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
