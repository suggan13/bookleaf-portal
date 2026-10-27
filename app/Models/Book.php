<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'books';

    protected $fillable = [
        'book_id',
        'author_id',
        'author_name',
        'title',
        'isbn',
        'genre',
        'publication_date',
        'status',
        'mrp',
        'author_royalty_per_copy',
        'total_copies_sold',
        'total_royalty_earned',
        'royalty_paid',
        'royalty_pending',
        'last_royalty_payout_date',
        'print_partner',
        'available_on',
    ];

    protected $casts = [
        'mrp' => 'float',
        'author_royalty_per_copy' => 'float',
        'total_copies_sold' => 'integer',
        'total_royalty_earned' => 'float',
        'royalty_paid' => 'float',
        'royalty_pending' => 'float',
        'available_on' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id', 'author_id');
    }
}