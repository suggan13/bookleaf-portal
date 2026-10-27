<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthorController extends Controller
{
    /**
     * Helper to write structured date-stamped logs
     */
    private function logAuthorAction(string $level, string $action, array $context = [], ?Throwable $exception = null): void
    {
        $user = Auth::user();
        $date = now()->format('Y-m-d');

        $payload = array_merge([
            'timestamp'   => now()->toIso8601String(),
            'action'      => $action,
            'user_id'     => $user ? ($user->_id ?? $user->id) : null,
            'author_id'   => $user->author_id ?? null,
            'email'       => $user->email ?? 'guest',
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ], $context);

        if ($exception) {
            $payload['error_message'] = $exception->getMessage();
            $payload['error_file']    = $exception->getFile() . ':' . $exception->getLine();
        }

        // Standard Laravel logger
        Log::channel('single')->{$level}("[AUTHOR-{$date}] {$action}", $payload);

        // Daily isolated log file: storage/logs/author-actions-YYYY-MM-DD.log
        try {
            Log::build([
                'driver' => 'single',
                'path'   => storage_path("logs/author-actions-{$date}.log"),
            ])->{$level}($action, $payload);
        } catch (Throwable $e) {
            // Prevent logger errors from interrupting user response
        }
    }

    /**
     * Get authenticated author's profile and books only.
     * Requirement: Each author should strictly see only their own data.
     */
    public function myBooks(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logAuthorAction('warning', 'UNAUTHORIZED_CATALOG_ACCESS');

                return response()->json([
                    'success' => false,
                    'message' => 'Author profile not linked or unauthorized account.'
                ], 403);
            }

            // Strict Isolation: Fetch only the current author's metadata and books
            $author = Author::where('author_id', $user->author_id)->first();
            $books  = Book::where('author_id', $user->author_id)->get();

            $this->logAuthorAction('info', 'FETCH_MY_BOOKS_SUCCESS', [
                'author_id'   => $user->author_id,
                'total_books' => $books->count()
            ]);

            return response()->json([
                'success' => true,
                'author'  => $author,
                'books'   => $books,
                'summary' => [
                    'total_titles'         => $books->count(),
                    'total_copies_sold'    => (int) $books->sum('total_copies_sold'),
                    'total_royalty_earned' => (float) $books->sum('total_royalty_earned'),
                    'royalty_paid'         => (float) $books->sum('royalty_paid'),
                    'royalty_pending'      => (float) $books->sum('royalty_pending'),
                ]
            ], 200);
        } catch (Throwable $e) {
            $this->logAuthorAction('error', 'FETCH_MY_BOOKS_CRITICAL_FAILURE', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load your catalog and royalty overview at this moment.'
            ], 500);
        }
    }
}