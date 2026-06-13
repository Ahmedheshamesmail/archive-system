<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'password',
        'role'
    ];

    protected $hidden = [
        'password'
    ];

    public function letters()
    {
        return $this->hasMany(
            Letter::class,
            'created_by'
        );
    }
}