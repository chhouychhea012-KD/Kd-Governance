<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessToken extends Model
{
    protected $table = 'user_access_tokens';
    protected $fillable = ['user_id', 'name', 'token', 'abilities', 'tokenable_id', 'tokenable_type', 'expires_at', 'last_used_at'];
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}