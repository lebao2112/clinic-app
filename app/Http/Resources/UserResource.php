<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'role_id' => $this->role_id, 
            'role' => $this->whenLoaded('role', function () {
                return $this->role->name; 
            }),
            
            'permissions' => $this->whenLoaded('role', function () {
                return $this->role->relationLoaded('permissions') 
                    ? $this->role->permissions->pluck('name') 
                    : [];
            }),
            
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}