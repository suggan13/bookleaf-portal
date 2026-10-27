<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookController extends Controller
{
    /**
     * Helper to write structured date-stamped logs
     */
    private function logBookAction(string $level, string $action, array $context = [], ?Throwable $exception = null): void
    {
        $user = Auth::user();
        $date = now()->format('Y-m-d');

        $payload = array_merge([
            'timestamp'  => now()->toIso8601String(),
            'action'     => $action,
            'user_id'    => $user ? ($user->_id ?? $user->id) : null,
            'role'       => $user->role ?? null,
            'email'      => $user->email ?? 'guest',
            'ip'         => request()->ip(),
        ], $context);

        if ($exception) {
            $payload['error_message'] = $exception->getMessage();
            $payload['error_file']    = $exception->getFile() . ':' . $exception->getLine();
        }

        // Standard Laravel logger
        Log::channel('single')->{$level}("[BOOKS-{$date}] {$action}", $payload);

        // Daily isolated log file: storage/logs/book-actions-YYYY-MM-DD.log
        try {
            Log::build([
                'driver' => 'single',
                'path'   => storage_path("logs/book-actions-{$date}.log"),
            ])->{$level}($action, $payload);
        } catch (Throwable $e) {
            // Prevent logger errors from interrupting user response
        }
    }

    /**
     * GET /api/admin/books
     * Returns catalog of books with optional filters
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'admin') {
                $this->logBookAction('warning', 'UNAUTHORIZED_BOOKS_CATALOG_ACCESS');

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin credentials required.'
                ], 403);
            }

            $query = Book::query();

            // Optional Genre filter
            if ($request->filled('genre')) {
                $query->where('genre', $request->genre);
            }

            // Optional Status filter (e.g. Published & Live, Typesetting, Proofreading)
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $books = $query->orderBy('book_id', 'asc')->get();

            $this->logBookAction('info', 'FETCH_BOOKS_CATALOG_SUCCESS', [
                'filters' => $request->only(['genre', 'status']),
                'count'   => $books->count()
            ]);

            return response()->json($books, 200);
        } catch (Throwable $e) {
            $this->logBookAction('error', 'FETCH_BOOKS_CATALOG_FAILED', [
                'filters' => $request->only(['genre', 'status'])
            ], $e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load books catalog at this moment.'
            ], 500);
        }
    }
}