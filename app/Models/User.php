<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'team_id',
        'profile_image',
        'sub_team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function subTeam()
    {
        return $this->belongsTo(Team::class, 'sub_team_id');
    }


    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the permissions for the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Get all permissions for the user (direct + role-based).
     */
    public function getAllPermissions()
    {
        // If user has super-admin role, return all permissions
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        $permissions = collect();

        // Add direct permissions
        $permissions = $permissions->merge($this->permissions);

        // Add permissions from roles
        foreach ($this->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    /**
     * Get all access tokens for the user.
     */
    public function userAccessTokens()
    {
        return $this->hasMany(UserAccessToken::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->roles->contains('slug', $role);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission($permission)
    {
        return $this->getAllPermissions()->contains('slug', $permission);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin()
    {
        return $this->roles->contains('slug', 'super-admin');
    }

    /**
     * Get the token for the user.
     */
    public function getToken()
    {
        $token = $this->userAccessTokens()->latest()->first();
        return $token ? $token->token : null;
    }

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ? asset('images/profile/' . $this->profile_image) : null;
    }
}
