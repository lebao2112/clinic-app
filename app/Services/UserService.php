<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Constants\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class UserService
{
    public function getUsers(Request $request)
    {
        $query = User::with('role');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                  ->orWhere('email', 'ilike', '%' . $search . '%');
            });
        }

        if ($request->has('role_id') && !empty($request->role_id)) {
            $query->where('role_id', $request->role_id);
        }

        return $query->orderBy('id', 'desc')->paginate($request->per_page ?? 15);
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $data['is_active'] ?? true; 
        
        return User::create($data);
    }

    public function updateUser(User $user, array $data)
    {
        $newRoleId = $data['role_id'] ?? $user->role_id;
        
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
        $this->ensureNotLastAdmin($user, $user->role_id, $isActive);

        $user->update(['is_active' => $isActive]);

        if (!$isActive) {
            $user->tokens()->delete();
        }

        return $user;
    }

    public function deleteUser(User $user)
    {
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
                            'role_id' => [Message::LAST_ADMIN_ACTION_DENIED]
                        ]
                    ], 422));
                }
            }
        }
    }
}