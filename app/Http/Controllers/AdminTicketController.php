<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Ticket;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminTicketController extends Controller
{
    /**
     * Helper to write structured date-stamped logs
     */
    private function logAdminAction(string $level, string $action, array $context = [], ?Throwable $exception = null): void
    {
        $admin = Auth::user();
        $date = now()->format('Y-m-d');

        $payload = array_merge([
            'timestamp'  => now()->toIso8601String(),
            'action'     => $action,
            'admin_id'   => $admin ? ($admin->_id ?? $admin->id) : null,
            'admin_email' => $admin->email ?? 'guest',
            'ip'         => request()->ip(),
        ], $context);

        if ($exception) {
            $payload['error_message'] = $exception->getMessage();
            $payload['error_file']    = $exception->getFile() . ':' . $exception->getLine();
        }

        // Standard Laravel logger
        Log::channel('single')->{$level}("[ADMIN-{$date}] {$action}", $payload);

        // Optional custom daily audit log file
        try {
            Log::build([
                'driver' => 'single',
                'path'   => storage_path("logs/admin-actions-{$date}.log"),
            ])->{$level}($action, $payload);
        } catch (Throwable $e) {
            // Failsafe to prevent logging errors from interrupting main response
        }
    }

    /**
     * Helper to verify Ops Admin role
     */
    private function verifyAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            $this->logAdminAction('warning', 'UNAUTHORIZED_ACCESS_ATTEMPT', [
                'user_id' => $user->id ?? null,
                'role'    => $user->role ?? null
            ]);
            abort(403, 'Unauthorized. Admin credentials required.');
        }
    }

    /**
     * GET /api/admin/authors
     */
    public function authors()
    {
        try {
            $this->verifyAdmin();

            $authors = Author::orderBy('author_id', 'asc')->get();

            $this->logAdminAction('info', 'FETCH_AUTHORS_DIRECTORY', [
                'count' => $authors->count()
            ]);

            return response()->json($authors, 200);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'FETCH_AUTHORS_DIRECTORY_FAILED', [], $e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load authors directory. Please try again later.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/tickets
     */
    public function index(Request $request)
    {
        try {
            $this->verifyAdmin();

            $query = Ticket::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('ticket_id', 'like', "%{$search}%")
                        ->orWhere('author_name', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            }

            $tickets = $query->orderBy('created_at', 'desc')->get();

            $this->logAdminAction('info', 'FETCH_TICKETS_QUEUE', [
                'filters' => $request->only(['status', 'priority', 'category', 'search']),
                'count'   => $tickets->count()
            ]);

            return response()->json($tickets, 200);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'FETCH_TICKETS_QUEUE_FAILED', [
                'filters' => $request->only(['status', 'priority', 'category', 'search'])
            ], $e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tickets queue.'
            ], 500);
        }
    }

    /**
     * GET /api/admin/tickets/{ticket_id}
     */
    public function show($ticket_id)
    {
        try {
            $this->verifyAdmin();

            $ticket = Ticket::where('ticket_id', $ticket_id)->first();

            if (!$ticket) {
                $this->logAdminAction('warning', 'TICKET_NOT_FOUND', ['ticket_id' => $ticket_id]);
                return response()->json([
                    'success' => false,
                    'message' => "Ticket {$ticket_id} not found."
                ], 404);
            }

            $this->logAdminAction('info', 'INSPECT_TICKET', ['ticket_id' => $ticket_id]);

            return response()->json($ticket, 200);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'INSPECT_TICKET_FAILED', ['ticket_id' => $ticket_id], $e);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ticket details.'
            ], 500);
        }
    }

    /**
     * PUT /api/admin/tickets/{ticket_id}
     */
    public function update(Request $request, $ticket_id)
    {
        try {
            $this->verifyAdmin();

            $ticket = Ticket::where('ticket_id', $ticket_id)->first();

            if (!$ticket) {
                $this->logAdminAction('warning', 'TICKET_UPDATE_NOT_FOUND', ['ticket_id' => $ticket_id]);
                return response()->json([
                    'success' => false,
                    'message' => "Ticket {$ticket_id} not found."
                ], 404);
            }

            $validated = $request->validate([
                'category'       => 'nullable|string',
                'priority'       => 'nullable|string|in:Critical,High,Medium,Low',
                'status'         => 'nullable|string|in:Open,In Progress,Resolved,Closed',
                'internal_notes' => 'nullable|string',
            ]);

            $ticket->update(array_filter([
                'category'       => $validated['category'] ?? $ticket->category,
                'priority'       => $validated['priority'] ?? $ticket->priority,
                'status'         => $validated['status'] ?? $ticket->status,
                'internal_notes' => $validated['internal_notes'] ?? $ticket->internal_notes,
            ]));

            $this->logAdminAction('info', 'TICKET_METADATA_UPDATED', [
                'ticket_id' => $ticket_id,
                'changes'   => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully.',
                'ticket'  => $ticket
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $ve->errors()
            ], 422);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'TICKET_UPDATE_FAILED', ['ticket_id' => $ticket_id], $e);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the ticket.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/tickets/{ticket_id}/reply
     */
    public function sendResponse(Request $request, $ticket_id)
    {
        try {
            $this->verifyAdmin();

            $validated = $request->validate([
                'response'       => 'required|string',
                'status'         => 'nullable|string|in:Open,In Progress,Resolved,Closed',
                'internal_notes' => 'nullable|string',
                'category'       => 'nullable|string',
                'priority'       => 'nullable|string',
            ]);

            $ticket = Ticket::where('ticket_id', $ticket_id)->first();

            if (!$ticket) {
                $this->logAdminAction('warning', 'REPLY_TICKET_NOT_FOUND', ['ticket_id' => $ticket_id]);
                return response()->json([
                    'success' => false,
                    'message' => "Ticket {$ticket_id} not found."
                ], 404);
            }

            $ticket->update([
                'admin_response' => $validated['response'],
                'status'         => $request->input('status', 'Resolved'),
                'internal_notes' => $request->input('internal_notes', $ticket->internal_notes),
                'category'       => $request->input('category', $ticket->category),
                'priority'       => $request->input('priority', $ticket->priority),
                'replied_at'     => now()->format('Y-m-d H:i'),
            ]);

            $this->logAdminAction('info', 'OFFICIAL_RESPONSE_DISPATCHED', [
                'ticket_id' => $ticket_id,
                'status'    => $ticket->status,
                'has_notes' => !empty($ticket->internal_notes)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Official response sent to author successfully.',
                'ticket'  => $ticket
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $ve->errors()
            ], 422);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'REPLY_DISPATCH_FAILED', ['ticket_id' => $ticket_id], $e);

            return response()->json([
                'success' => false,
                'message' => 'Could not dispatch response to author.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/tickets/{ticket_id}/assign
     */
    public function assignTicket(Request $request, $ticket_id)
    {
        try {
            $this->verifyAdmin();
            $admin = Auth::user();

            $ticket = Ticket::where('ticket_id', $ticket_id)->first();

            if (!$ticket) {
                $this->logAdminAction('warning', 'ASSIGN_TICKET_NOT_FOUND', ['ticket_id' => $ticket_id]);
                return response()->json([
                    'success' => false,
                    'message' => "Ticket {$ticket_id} not found."
                ], 404);
            }

            // Agar ticket open hai, toh assign hone par in_progress set karein
            $newStatus = ($ticket->status === 'open') ? 'in_progress' : $ticket->status;

            $ticket->update([
                'assigned_to'   => $admin->_id ?? $admin->id,
                'assigned_name' => $admin->name,
                'status'        => $newStatus,
            ]);

            $this->logAdminAction('info', 'TICKET_ASSIGNED_TO_SELF', [
                'ticket_id'     => $ticket_id,
                'assigned_to'   => $admin->_id ?? $admin->id,
                'assigned_name' => $admin->name,
                'status'        => $newStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket assigned successfully.',
                'ticket'  => $ticket->fresh()
            ], 200);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'ASSIGN_TICKET_FAILED', ['ticket_id' => $ticket_id], $e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign ticket.'
            ], 500);
        }
    }

    /**
     * POST /api/admin/tickets/{ticket_id}/regenerate-draft
     */
    public function regenerateDraft(Request $request, $ticket_id, AIService $aiService)
    {
        try {
            $this->verifyAdmin();

            $ticket = Ticket::where('ticket_id', $ticket_id)->first();

            if (!$ticket) {
                $this->logAdminAction('warning', 'REGENERATE_DRAFT_NOT_FOUND', ['ticket_id' => $ticket_id]);
                return response()->json([
                    'success' => false,
                    'message' => "Ticket {$ticket_id} not found."
                ], 404);
            }

            $category = $request->input('category', $ticket->category);

            $draft = $aiService->generateDraftResponse([
                'author_name' => $ticket->author_name,
                'book_title'  => $ticket->book_title,
                'subject'     => $ticket->subject,
                'description' => $ticket->description,
                'category'    => $category,
            ]);

            $this->logAdminAction('info', 'AI_DRAFT_REGENERATED', [
                'ticket_id' => $ticket_id,
                'category'  => $category
            ]);

            return response()->json([
                'success'           => true,
                'ai_draft_response' => $draft
            ], 200);
        } catch (Throwable $e) {
            $this->logAdminAction('error', 'REGENERATE_DRAFT_FAILED', ['ticket_id' => $ticket_id], $e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate AI draft.'
            ], 500);
        }
    }
}
