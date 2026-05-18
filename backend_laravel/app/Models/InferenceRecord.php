<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InferenceRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'notes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function visitorSession()
    {
        return $this->belongsTo(VisitorSession::class);
    }
}
