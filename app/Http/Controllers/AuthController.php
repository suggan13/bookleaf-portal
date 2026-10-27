<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Author;
use App\Models\Book;
use App\Models\Ticket;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    protected ?AIService $aiService;

    public function __construct(?AIService $aiService = null)
    {
        $this->aiService = $aiService;
    }

    /**
     * Helper to write structured date-stamped logs
     */
    private function logAuthAction(string $level, string $action, array $context = [], ?Throwable $exception = null): void
    {
        $user = Auth::user();
        $date = now()->format('Y-m-d');

        $payload = array_merge([
            'timestamp'   => now()->toIso8601String(),
            'action'      => $action,
            'user_id'     => $user ? ($user->_id ?? $user->id) : null,
            'author_id'   => $user->author_id ?? null,
            'email'       => $user->email ?? (request()->input('email') ?? 'guest'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ], $context);

        if ($exception) {
            $payload['error_message'] = $exception->getMessage();
            $payload['error_file']    = $exception->getFile() . ':' . $exception->getLine();
        }

        // Standard Laravel single log channel
        Log::channel('single')->{$level}("[AUTH-{$date}] {$action}", $payload);

        // Daily isolated log file: storage/logs/auth-actions-YYYY-MM-DD.log
        try {
            Log::build([
                'driver' => 'single',
                'path'   => storage_path("logs/auth-actions-{$date}.log"),
            ])->{$level}($action, $payload);
        } catch (Throwable $e) {
            // Prevent logger failures from interrupting core authentication flow
        }
    }

    /**
     * User Login Method
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // Session fixation prevent karein aur fresh session create karein
            $request->session()->regenerate();
            $request->session()->save(); // <--- Zaroori: Session ko cookie mein turant commit karein

            return response()->json([
                'success' => true,
                'user'    => Auth::user(),
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password.'
        ], 401);
    }

    /**
     * Get Authenticated User Details
     */
    public function me(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['user' => null], 401);
            }

            $user = Auth::user();
            return response()->json([
                'user' => [
                    'id'        => $user->_id ?? $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'role'      => $user->role,
                    'author_id' => $user->author_id,
                ]
            ], 200);
        } catch (Throwable $e) {
            $this->logAuthAction('error', 'AUTH_ME_SESSION_ERROR', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve user session.'
            ], 500);
        }
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            $this->logAuthAction('info', 'USER_LOGOUT', [
                'user_id' => $user ? ($user->_id ?? $user->id) : null
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);
        } catch (Throwable $e) {
            $this->logAuthAction('error', 'LOGOUT_FAILED', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Logout operation encountered an issue.'
            ], 500);
        }
    }

    /**
     * Author Books & Royalty Overview (Strictly Isolated to Logged-in Author)
     */
    public function myBooks()
    {
        try {
            $user = Auth::user();

            // Security check: Only authors with a linked author_id allowed
            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logAuthAction('warning', 'UNAUTHORIZED_MY_BOOKS_ACCESS');

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Only authors can access this data.'
                ], 403);
            }

            // Strict Isolation: Filter ONLY by current user's author_id
            $author = Author::where('author_id', $user->author_id)->first();
            $books  = Book::where('author_id', $user->author_id)->get();

            $this->logAuthAction('info', 'FETCH_AUTHOR_BOOKS_CATALOG', [
                'author_id'   => $user->author_id,
                'total_books' => $books->count()
            ]);

            return response()->json([
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
            $this->logAuthAction('error', 'FETCH_MY_BOOKS_FAILED', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve your books and royalties catalog.'
            ], 500);
        }
    }

    /**
     * Get only logged-in author's support tickets
     */
    public function myTickets()
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logAuthAction('warning', 'UNAUTHORIZED_MY_TICKETS_ACCESS');

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            // Strict Isolation: Fetch only tickets belonging to this author
            $tickets = Ticket::where('author_id', $user->author_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $this->logAuthAction('info', 'FETCH_AUTHOR_TICKETS_SUCCESS', [
                'author_id'     => $user->author_id,
                'tickets_count' => $tickets->count()
            ]);

            return response()->json($tickets, 200);
        } catch (Throwable $e) {
            $this->logAuthAction('error', 'FETCH_MY_TICKETS_FAILED', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Could not load tickets at this moment.'
            ], 500);
        }
    }

    /**
     * Store new support ticket with Live LLM/Fallback Triage & Policy Drafting
     */
    public function storeTicket(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logAuthAction('warning', 'UNAUTHORIZED_STORE_TICKET_ATTEMPT');
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $validated = $request->validate([
                'subject'     => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'book_id'     => 'nullable|string',
                'attachment'  => 'nullable|string',
            ]);

            $bookTitle = 'General / Account Level';
            if (!empty($validated['book_id']) && $validated['book_id'] !== 'general') {
                $book = Book::where('book_id', $validated['book_id'])
                    ->where('author_id', $user->author_id)
                    ->first();
                if ($book) {
                    $bookTitle = $book->title;
                }
            }

            // 1. Live LLM Triage via AIService (with heuristic fallback)
            $aiService = $this->aiService ?? app(AIService::class);
            $triage = $aiService->triageTicket(
                $validated['subject'],
                $validated['description'],
                $bookTitle
            );

            // 2. Policy-Grounded Draft Generation
            $draft = $aiService->generateDraftResponse([
                'author_name' => $user->name,
                'book_title'  => $bookTitle,
                'subject'     => $validated['subject'],
                'description' => $validated['description'],
                'category'    => $triage['category'],
            ]);

            $ticketCount = Ticket::count();
            $ticketId = 'TCK-' . (1001 + $ticketCount);

            $ticket = Ticket::create([
                'ticket_id'          => $ticketId,
                'author_id'          => $user->author_id,
                'author_name'        => $user->name,
                'book_id'            => ($validated['book_id'] === 'general') ? null : $validated['book_id'],
                'book_title'         => $bookTitle,
                'subject'            => $validated['subject'],
                'description'        => $validated['description'],
                'attachment'         => $request->input('attachment', null),
                'category'           => $triage['category'],
                'priority'           => $triage['priority'],
                'status'             => 'Open',
                'ai_draft_response'  => $draft,
                'admin_response'     => null,
                'internal_notes'     => null,
                'assigned_to'        => null,
                'assigned_name'      => null,
                'created_at'         => now()->format('Y-m-d H:i'),
            ]);

            $this->logAuthAction('info', 'TICKET_CREATED_SUCCESSFULLY', [
                'ticket_id' => $ticketId,
                'author_id' => $user->author_id,
                'category'  => $ticket->category,
                'priority'  => $ticket->priority,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully',
                'ticket'  => $ticket
            ], 201);
        } catch (ValidationException $ve) {
            $this->logAuthAction('warning', 'STORE_TICKET_VALIDATION_FAILED', ['errors' => $ve->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $ve->errors()
            ], 422);
        } catch (Throwable $e) {
            $this->logAuthAction('error', 'STORE_TICKET_CREATION_FAILED', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating your ticket. Please retry.'
            ], 500);
        }
    }
}
