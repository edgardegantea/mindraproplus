<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function inferenceRecords()
    {
        return $this->hasManyThrough(InferenceRecord::class, User::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
