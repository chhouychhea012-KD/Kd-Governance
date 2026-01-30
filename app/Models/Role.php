<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'level'];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Get the permissions for the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if this role is super-admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->slug === 'super-admin';
    }

    /**
     * Get all permissions for this role.
     * If super-admin, return all permissions from the permissions table.
     */
    public function getAllPermissions()
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        return $this->permissions;
    }

    /**
     * Get the permission IDs for this role.
     * If super-admin, return all permission IDs.
     */
    public function getPermissionIds()
    {
        if ($this->isSuperAdmin()) {
            return Permission::pluck('id')->toArray();
        }

        return $this->permissions()->pluck('permissions.id')->toArray();
    }
}
