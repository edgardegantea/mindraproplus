<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrisisEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'probability'   => 'float',
        'email_sent'    => 'boolean',
        'email_sent_at' => 'datetime',
        'notes'         => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inferenceRecord()
    {
        return $this->belongsTo(InferenceRecord::class);
    }
}
