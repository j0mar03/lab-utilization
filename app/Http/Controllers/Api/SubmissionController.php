<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Room;
use App\Models\Tool;
use App\Models\Transaction;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SubmissionController
 *
 * Handles POST requests from Google Apps Script when a Google Form is submitted.
 *
 * Endpoint: POST /api/submission
 * Auth:     X-Api-Key header (shared secret, set in .env as API_SECRET_KEY)
 *
 * Google Form fields (Phase 1):
 *   - First Name (borrower_name)
 *   - Room to Use (room_name — matched to rooms table)
 *   - Subject     (subject)
 *
 * Submission types:
 *   room_checkout  — faculty is checking out a room
 *   tool_checkout  — faculty/assistant is checking out a tool
 *   return         — an item has been returned
 */
class SubmissionController extends Controller
{
    public function __construct(private TelegramService $telegram)
    {
    }

    /**
     * Handle an incoming Google Form submission.
     */
    public function store(StoreSubmissionRequest $request): JsonResponse
    {
        // Step 1: Verify API key
        if (! $this->isValidApiKey($request)) {
            Log::warning('SubmissionController: Invalid or missing API key', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        return match ($request->input('type')) {
            'room_checkout', 'tool_checkout' => $this->handleCheckout($request),
            'return'                          => $this->handleReturn($request),
            default => response()->json(['success' => false, 'message' => 'Unknown type.'], 422),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Checkout handler
    // ─────────────────────────────────────────────────────────────────────────

    private function handleCheckout(StoreSubmissionRequest $request): JsonResponse
    {
        $type = $request->input('type');
        $room = null;
        $tool = null;

        // ── Resolve room by name ──────────────────────────────────────────
        if ($type === 'room_checkout') {
            $room = Room::where('name', $request->input('room_name'))->first();

            if (! $room) {
                return response()->json([
                    'success' => false,
                    'message' => "Room '{$request->input('room_name')}' not found. Check the room name in your Google Form matches the system exactly.",
                ], 422);
            }
        }

        // ── Resolve tool by name ──────────────────────────────────────────
        if ($type === 'tool_checkout') {
            $tool = Tool::active()->where('name', $request->input('tool_name'))->first();

            if (! $tool) {
                return response()->json([
                    'success' => false,
                    'message' => "Tool '{$request->input('tool_name')}' not found or is inactive. Ask the Lab Head to add it in Admin → Tools.",
                ], 422);
            }

            // Check quantity availability
            $quantity  = (int) $request->input('quantity', 1);
            $available = $tool->available_quantity;

            if ($available < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$available} unit(s) of '{$tool->name}' available. You requested {$quantity}.",
                ], 422);
            }
        }

        // ── Create the transaction ────────────────────────────────────────
        $transaction = DB::transaction(function () use ($request, $room, $tool) {
            return Transaction::create([
                'user_id'            => auth()->id() ?? 1,
                'room_id'            => $room?->id,
                'tool_id'            => $tool?->id,
                'quantity'           => (int) $request->input('quantity', 1),
                'borrower_name'      => $request->input('borrower_name'),
                'borrower_email'     => $request->input('borrower_email'),
                'subject'            => $request->input('subject'),
                'checked_out_at'     => now(),
                'expected_return_at' => $request->filled('expected_return')
                                            ? now()->parse($request->input('expected_return'))
                                            : null,
                'status'             => 'open',
                'notes'              => $request->input('notes'),
                'source'             => 'google_form',
            ]);
        });

        $transaction->load(['room', 'tool']);

        // ── Send Telegram notification ────────────────────────────────────
        $this->telegram->sendCheckoutNotification($transaction);

        Log::info('New checkout via Google Form', [
            'transaction_id' => $transaction->id,
            'borrower'       => $transaction->borrower_name,
            'subject'        => $transaction->subject,
            'type'           => $type,
        ]);

        return response()->json([
            'success'        => true,
            'message'        => 'Checkout recorded successfully.',
            'transaction_id' => $transaction->id,
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Return handler
    // ─────────────────────────────────────────────────────────────────────────

    private function handleReturn(StoreSubmissionRequest $request): JsonResponse
    {
        $transaction = Transaction::with(['room', 'tool'])
            ->find($request->input('return_for_id'));

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => "Transaction #{$request->input('return_for_id')} not found.",
            ], 404);
        }

        if ($transaction->status === 'returned') {
            return response()->json([
                'success' => false,
                'message' => "Transaction #{$transaction->id} is already marked as returned.",
            ], 422);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'returned_at' => now(),
                'status'      => 'returned',
            ]);
        });

        $transaction->refresh()->load(['room', 'tool']);

        $this->telegram->sendReturnNotification($transaction);

        Log::info('Return recorded via Google Form', [
            'transaction_id' => $transaction->id,
            'borrower'       => $transaction->borrower_name,
        ]);

        return response()->json(['success' => true, 'message' => 'Return recorded successfully.']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API key verification
    // ─────────────────────────────────────────────────────────────────────────

    private function isValidApiKey(Request $request): bool
    {
        $configuredKey = env('API_SECRET_KEY', '');

        if (empty($configuredKey)) {
            Log::error('SubmissionController: API_SECRET_KEY is not set in .env. All submissions rejected.');
            return false;
        }

        return hash_equals($configuredKey, (string) $request->header('X-Api-Key', ''));
    }
}
