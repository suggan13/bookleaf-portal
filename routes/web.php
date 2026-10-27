<?php

use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (BookLeaf Support Portal)
|--------------------------------------------------------------------------
*/

// 1. Single Page Application Entrypoint
Route::get('/', function () {
    return view('portal');
})->name('portal');

// 2. Authentication APIs (Session Cookie Driven)
Route::prefix('api/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// 3. Author Protected Workspace (Strict Isolation: Role author)
Route::middleware(['author'])->prefix('api/author')->group(function () {
    Route::get('/my-books', [AuthorController::class, 'myBooks']);
    Route::get('/tickets', [TicketController::class, 'myTickets']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket_id}', [TicketController::class, 'show']);
    
    // Multi-turn Conversation & Lifecycle Transitions
    Route::post('/tickets/{ticket_id}/reply', [TicketController::class, 'replyTicket']);
    Route::post('/tickets/{ticket_id}/close', [TicketController::class, 'closeTicket']);
});

// 4. Admin Ops Desk (Role: admin)
Route::middleware(['admin'])->prefix('api/admin')->group(function () {
    Route::get('/authors', [AdminTicketController::class, 'authors']);
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/tickets', [AdminTicketController::class, 'index']);
    Route::get('/tickets/{ticket_id}', [AdminTicketController::class, 'show']);
    Route::put('/tickets/{ticket_id}', [AdminTicketController::class, 'update']);
    Route::post('/tickets/{ticket_id}/reply', [AdminTicketController::class, 'sendResponse']);
    Route::post('/tickets/{ticket_id}/assign', [AdminTicketController::class, 'assignTicket']);
    
    // AI Draft Studio (Context-Aware Regeneration)
    Route::post('/tickets/{ticket_id}/regenerate-draft', [AdminTicketController::class, 'regenerateDraft']);
});