<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'authors';

    protected $fillable = [
        'author_id',
        'name',
        'email',
        'phone',
        'city',
        'joined_date',
        'total_books',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id', 'author_id');
    }
}