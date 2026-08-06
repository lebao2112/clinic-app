<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    
    // Add display_name (T1.7) and name (T1.5) to fillable
    protected $fillable = ['name', 'display_name'];

    // Many-to-Many relationship with permissions (T1.5)
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    // One-to-Many relationship with users (T1.7)
    public function users()
    {
        return $this->hasMany(User::class);
    }
}