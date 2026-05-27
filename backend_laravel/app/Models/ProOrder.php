<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProOrder extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_at'      => 'datetime',
            'reviewed_at'  => 'datetime',
            'amount_cents' => 'integer',
            'notes'        => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPlus(): bool
    {
        return $this->plan_slug === 'plus';
    }

    public function isInquiry(): bool
    {
        return $this->status === 'inquiry';
    }
}
