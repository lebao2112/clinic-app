<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Logs when a new user account is registered in the system.
     */
    public function created(User $user): void
    {
        ActivityLog::create([
            'user_id'      => Auth::check() ? Auth::id() : null, 
            'subject_type' => 'user',
            'subject_id'   => $user->id,
            'action'       => 'registered',
            'meta'         => [
                'name'    => $user->name,
                'email'   => $user->email,
                'role_id' => $user->role_id,
            ]
        ]);
    }

    /**
     * Handle the User "updated" event.
     * Logs when critical account information (like role_id or is_active) changes.
     */
    public function updated(User $user): void
    {
        if ($user->isDirty('role_id')) {
            ActivityLog::create([
                'user_id'      => Auth::check() ? Auth::id() : null,
                'subject_type' => 'user',
                'subject_id'   => $user->id,
                'action'       => 'role_changed',
                'meta'         => [
                    'before' => ['role_id' => $user->getOriginal('role_id')],
                    'after'  => ['role_id' => $user->role_id],
                ]
            ]);
        }
        if ($user->isDirty('is_active')) {
            ActivityLog::create([
                'user_id'      => Auth::check() ? Auth::id() : null,
                'subject_type' => 'user',
                'subject_id'   => $user->id,
                'action'       => 'status_changed',
                'meta'         => [
                    'before' => ['is_active' => $user->getOriginal('is_active')],
                    'after'  => ['is_active' => $user->is_active],
                ]
            ]);
        }
    }
}