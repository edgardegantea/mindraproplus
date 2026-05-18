<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProOrder extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount_cents' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
