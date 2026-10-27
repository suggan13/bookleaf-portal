<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'tickets';

    protected $fillable = [
        'ticket_id',          // e.g. TCK-1001
        'author_id',          // e.g. AUTH001
        'author_name',
        'book_id',            // BK001 ya null (General / Account Level)
        'book_title',         // Book name ya 'General / Account Level'
        'subject',
        'description',
        'category',           // AI auto-classification (Phase 3 me integrate hoga)
        'priority',           // Critical, High, Medium, Low
        'status',             // Open, In Progress, Resolved, Closed
        'ai_draft_response',  // AI generated draft for ops team
        'admin_response',     // Actual response sent to author
        'internal_notes',     // Ops internal notes
        'assigned_to',        // Admin user
        'messages',           // <--- Array of multi-turn chat objects
        'replied_at',
        'assigned_to',   // <-- Add this
        'assigned_name', // <-- Add this
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'replied_at' => 'datetime',
        'messages' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id', 'author_id');
    }
}
