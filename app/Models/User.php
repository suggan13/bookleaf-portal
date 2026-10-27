<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // 'admin' or 'author'
        'author_id', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}