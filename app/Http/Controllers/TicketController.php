<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Ticket;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Helper to write structured date-stamped logs
     */
    private function logTicketAction(string $level, string $action, array $context = [], ?Throwable $exception = null): void
    {
        $user = Auth::user();
        $date = now()->format('Y-m-d');

        $payload = array_merge([
            'timestamp'  => now()->toIso8601String(),
            'action'     => $action,
            'user_id'    => $user ? ($user->_id ?? $user->id) : null,
            'author_id'  => $user->author_id ?? null,
            'email'      => $user->email ?? 'guest',
            'ip'         => request()->ip(),
        ], $context);

        if ($exception) {
            $payload['error_message'] = $exception->getMessage();
            $payload['error_file']    = $exception->getFile() . ':' . $exception->getLine();
        }

        // Standard Laravel single log channel
        Log::channel('single')->{$level}("[TICKET-{$date}] {$action}", $payload);

        // Daily isolated log file: storage/logs/ticket-actions-YYYY-MM-DD.log
        try {
            Log::build([
                'driver' => 'single',
                'path'   => storage_path("logs/ticket-actions-{$date}.log"),
            ])->{$level}($action, $payload);
        } catch (Throwable $e) {
            // Prevent logger errors from interrupting user response
        }
    }

    /**
     * Author: List own submitted tickets (Strictly Isolated)
     */
    public function myTickets(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logTicketAction('warning', 'UNAUTHORIZED_MY_TICKETS_ACCESS');

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            $tickets = Ticket::where('author_id', $user->author_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $this->logTicketAction('info', 'FETCH_OWN_TICKETS_SUCCESS', [
                'count' => $tickets->count()
            ]);

            return response()->json($tickets, 200);
        } catch (Throwable $e) {
            $this->logTicketAction('error', 'FETCH_OWN_TICKETS_FAILED', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load tickets at this moment.'
            ], 500);
        }
    }

    /**
     * Author: Submit a new support query with Policy-Grounded AI Triage
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logTicketAction('warning', 'UNAUTHORIZED_STORE_TICKET_ATTEMPT');

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            $validated = $request->validate([
                'subject'     => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'book_id'     => 'nullable|string',
                'attachment'  => 'nullable|string',
            ]);

            // Resolve Book Title
            $bookTitle = 'General / Account Level';
            if (!empty($validated['book_id']) && $validated['book_id'] !== 'general') {
                $book = Book::where('book_id', $validated['book_id'])
                    ->where('author_id', $user->author_id)
                    ->first();
                if ($book) {
                    $bookTitle = $book->title;
                }
            }

            // 1. Live LLM Triage (Category & Priority)
            $triage = $this->aiService->triageTicket(
                $validated['subject'],
                $validated['description'],
                $bookTitle
            );

            // 2. Generate Policy-Grounded Draft
            $aiDraft = $this->aiService->generateDraftResponse([
                'author_name' => $user->name,
                'book_title'  => $bookTitle,
                'subject'     => $validated['subject'],
                'description' => $validated['description'],
                'category'    => $triage['category'],
            ]);

            // Sequence ID generate karein (TCK-0001, TCK-0002...)
            $ticketCount = Ticket::count() + 1;
            $ticketId = 'TCK-' . str_pad((string)$ticketCount, 4, '0', STR_PAD_LEFT);

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
                'ai_draft_response'  => $aiDraft,
                'admin_response'     => null,
                'internal_notes'     => null,
                'assigned_to'        => null,
                'assigned_name'      => null,
                'created_at'         => now()->toIso8601String(),
            ]);

            $this->logTicketAction('info', 'TICKET_CREATED_SUCCESS', [
                'ticket_id' => $ticketId,
                'category'  => $ticket->category,
                'priority'  => $ticket->priority,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support query submitted successfully.',
                'ticket'  => $ticket,
            ], 201);
        } catch (ValidationException $ve) {
            $this->logTicketAction('warning', 'STORE_TICKET_VALIDATION_ERROR', ['errors' => $ve->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $ve->errors()
            ], 422);
        } catch (Throwable $e) {
            $this->logTicketAction('error', 'STORE_TICKET_CRITICAL_FAILURE', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the ticket. Please try again.'
            ], 500);
        }
    }

    /**
     * Author: Get single ticket details
     */
    public function show($ticket_id)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'author' || !$user->author_id) {
                $this->logTicketAction('warning', 'UNAUTHORIZED_SHOW_TICKET_ACCESS', ['ticket_id' => $ticket_id]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            $ticket = Ticket::where('ticket_id', $ticket_id)
                ->where('author_id', $user->author_id)
                ->first();

            if (!$ticket) {
                $this->logTicketAction('warning', 'TICKET_NOT_FOUND_OR_FORBIDDEN', ['ticket_id' => $ticket_id]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found or access denied.'
                ], 404);
            }

            $this->logTicketAction('info', 'FETCH_SINGLE_TICKET_SUCCESS', ['ticket_id' => $ticket_id]);

            return response()->json($ticket, 200);
        } catch (Throwable $e) {
            $this->logTicketAction('error', 'FETCH_SINGLE_TICKET_FAILED', ['ticket_id' => $ticket_id], $e);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ticket details.'
            ], 500);
        }
    }
}