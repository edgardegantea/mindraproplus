<?php

namespace App\Policies;

use App\Models\InferenceRecord;
use App\Models\User;

class InferencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, InferenceRecord $record): bool
    {
        return $record->user_id === $user->id || $user->isAdmin();
    }
}
