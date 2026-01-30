<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'parent_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Team::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Team::class, 'parent_id');
    }
}
