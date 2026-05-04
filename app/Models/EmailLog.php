<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['recipient_email', 'subject', 'type', 'sent_at', 'status', 'error_message'];
    protected $casts = ['sent_at' => 'datetime'];
}
