<?php

use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// 1. Frontend View
Route::get('/', function () {
    return view('portal');
});

// 2. Authentication (Wrapped with 'web' to maintain session cookies)
Route::middleware(['web'])->prefix('api/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// 3. Author Protected Routes (Role: author)
Route::middleware(['web', 'author'])->prefix('api/author')->group(function () {
    Route::get('/my-books', [AuthController::class, 'myBooks']); // Ya [AuthorController::class, 'myBooks']
    Route::get('/tickets', [TicketController::class, 'myTickets']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{ticket_id}', [TicketController::class, 'show']);
});

// 4. Admin Protected Routes (Role: admin)
Route::middleware(['web', 'admin'])->prefix('api/admin')->group(function () {
    Route::get('/authors', [AdminTicketController::class, 'authors']);
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/tickets', [AdminTicketController::class, 'index']);
    Route::get('/tickets/{ticket_id}', [AdminTicketController::class, 'show']);
    Route::put('/tickets/{ticket_id}', [AdminTicketController::class, 'update']);
    Route::post('/tickets/{ticket_id}/reply', [AdminTicketController::class, 'sendResponse']);
    Route::post('/tickets/{ticket_id}/assign', [AdminTicketController::class, 'assignTicket']);
    
    // AI Live Draft Regeneration Route
    Route::post('/tickets/{ticket_id}/regenerate-draft', [AdminTicketController::class, 'regenerateDraft']);
});

use Illuminate\Support\Facades\Artisan;

Route::get('/run-database-seed-atlas', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'output' => Artisan::output(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});