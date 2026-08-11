<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Constants\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserService
{
    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $data['is_active'] ?? true; 
        
        return User::create($data);
    }

    public function updateUser(User $user, array $data)
    {
        $newRoleId = $data['role_id'] ?? $user->role_id;
        
        // Check Admin protection (keeping the current is_active status)
        $this->ensureNotLastAdmin($user, $newRoleId, $user->is_active);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); 
        }

        $user->update($data);
        return $user;
    }

    public function updateStatus(User $user, bool $isActive)
    {
        // Check Admin protection before changing status
        $this->ensureNotLastAdmin($user, $user->role_id, $isActive);

        $user->update(['is_active' => $isActive]);
        return $user;
    }

    public function deleteUser(User $user)
    {
        // Check Admin protection before deleting (treating deletion as deactivation)
        $this->ensureNotLastAdmin($user, $user->role_id, false);

        $user->delete();
    }

    private function ensureNotLastAdmin(User $user, int $newRoleId, bool $newIsActive)
    {
        $adminRole = Role::where('name', 'ADMIN')->first();

        if (!$adminRole) return; 

        if ($user->role_id === $adminRole->id && $user->is_active) {
            if ($newRoleId !== $adminRole->id || !$newIsActive) {
                $activeAdminCount = User::where('role_id', $adminRole->id)
                                        ->where('is_active', true)
                                        ->count();
                
                if ($activeAdminCount <= 1) {
                    throw new HttpResponseException(response()->json([
                        'success' => false,
                        'message' => Message::LAST_ADMIN_ACTION_DENIED, 
                        'errors'  => [
                            'role_id' => ['You are the last active ADMIN in the system.']
                        ]
                    ], 422));
                }
            }
        }
    }
}