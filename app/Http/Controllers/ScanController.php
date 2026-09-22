<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Tool;
use App\Models\Transaction;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ScanController — Public QR Code Checkout/Return Flow
 *
 * These routes are PUBLIC (no login required). Authentication is implicit:
 * the person physically holding the QR code is in the lab.
 *
 * Rate limiting is applied in routes/web.php to prevent abuse.
 *
 * Flow:
 *   1. Lab Head prints QR codes from /admin/qr/rooms or /admin/qr/tools
 *   2. QR code is posted on the room door or taped to the tool
 *   3. Faculty scans QR → mobile form → fills name + subject → submits
 *   4. Transaction created → success page shows Transaction ID + Return QR
 *   5. When returning: scan Return QR or open the return link → confirm → done
 */
class ScanController extends Controller
{
    public function __construct(private TelegramService $telegram)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Room scan pages
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show the mobile checkout page for a specific room.
     * GET /scan/room/{room}
     */
    public function room(Room $room): View
    {
        // Find any currently open or overdue transactions for this room
        $openTransactions = $room->transactions()
            ->whereIn('status', ['open', 'partially_returned', 'overdue'])
            ->with('user')
            ->latest('checked_out_at')
            ->get();

        $faculties = Faculty::where('is_active', true)->orderBy('department')->orderBy('name')->get();
        $subjects  = Subject::where('is_active', true)->orderBy('department')->orderBy('code')->get();
        $softwareCatalog = Transaction::SOFTWARE_CATALOG;

        return view('scan.room', compact('room', 'openTransactions', 'faculties', 'subjects', 'softwareCatalog'));
    }

    /**
     * Process a room checkout from the scan form.
     * POST /scan/room/{room}
     */
    public function checkoutRoom(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'borrower_name'  => ['required', 'string', 'max:255'],
            'borrower_email' => ['nullable', 'email', 'max:255'],
            'subject'        => ['required', 'string', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ], [
            'borrower_name.required' => 'Please enter your name.',
            'subject.required'       => 'Please enter the subject/purpose.',
        ]);

        // Prevent accidental double-tap duplicate within 30 seconds
        $recentDuplicate = Transaction::where('room_id', $room->id)
            ->where('borrower_name', $validated['borrower_name'])
            ->where('checked_out_at', '>=', now()->subSeconds(30))
            ->latest('id')
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('scan.success', $recentDuplicate->id);
        }

        // ── Room Occupancy Conflict Handling ────────────────────────────
        $activeTransactions = $room->transactions()
            ->whereIn('status', ['open', 'partially_returned', 'overdue'])
            ->get();

        if ($activeTransactions->isNotEmpty()) {
            $resolution = $request->input('conflict_resolution', 'end_previous');

            if ($resolution === 'end_previous') {
                foreach ($activeTransactions as $activeTx) {
                    $activeTx->update([
                        'status'      => 'returned',
                        'returned_at' => now(),
                        'notes'       => trim(($activeTx->notes ? $activeTx->notes . "\n" : '') . "[QR Scan: Auto-ended upon handover to {$validated['borrower_name']}]"),
                    ]);

                    foreach ($activeTx->items as $prevItem) {
                        if ($prevItem->status !== 'returned') {
                            $prevItem->update([
                                'quantity_returned' => $prevItem->quantity_borrowed,
                                'status'            => 'returned',
                                'returned_at'       => now(),
                            ]);
                        }
                    }

                    $activeTx->load(['room', 'tool', 'items.tool']);
                    $this->telegram->sendReturnNotification($activeTx);
                }
            }
        }

        $softwareUtilized = null;
        if ($room->isComputerLab()) {
            $softwareList = (array) $request->input('software_utilized', []);
            if ($request->filled('software_custom')) {
                $customItems = array_filter(array_map('trim', explode(',', $request->input('software_custom'))));
                foreach ($customItems as $cItem) {
                    if (!in_array($cItem, $softwareList)) {
                        $softwareList[] = $cItem;
                    }
                }
            }
            $softwareUtilized = array_values(array_unique(array_filter($softwareList)));

            if (empty($softwareUtilized)) {
                return back()->withErrors([
                    'software_utilized' => 'Software utilized is required when checking out Computer Laboratory ' . $room->name . '.',
                ])->withInput();
            }
        }

        $transaction = Transaction::create([
            'user_id'           => 1, // system user — no login on scan pages
            'room_id'           => $room->id,
            'tool_id'           => null,
            'quantity'          => 1,
            'borrower_name'     => $validated['borrower_name'],
            'borrower_email'    => $validated['borrower_email'] ?? null,
            'subject'           => $validated['subject'],
            'software_utilized' => $softwareUtilized,
            'checked_out_at'    => now(),
            'status'            => 'open',
            'notes'             => $validated['notes'] ?? null,
            'source'            => 'qr_scan',
        ]);

        $transaction->load('room');
        $this->telegram->sendCheckoutNotification($transaction);

        Log::info('QR room checkout', [
            'transaction_id' => $transaction->id,
            'room'           => $room->name,
            'borrower'       => $transaction->borrower_name,
            'subject'        => $transaction->subject,
        ]);

        return redirect()->route('scan.success', $transaction->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tool scan pages
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show the mobile checkout page for a specific tool.
     * GET /scan/tool/{tool}
     */
    public function tool(Tool $tool): View
    {
        abort_if(! $tool->is_active, 404, 'This tool is not currently available.');

        $openTransactions = $tool->transactions()
            ->where('status', 'open')
            ->latest('checked_out_at')
            ->get();

        $availableQty = $tool->available_quantity;
        $faculties    = Faculty::where('is_active', true)->orderBy('department')->orderBy('name')->get();
        $subjects     = Subject::where('is_active', true)->orderBy('department')->orderBy('code')->get();

        return view('scan.tool', compact('tool', 'openTransactions', 'availableQty', 'faculties', 'subjects'));
    }

    /**
     * Process a tool checkout from the scan form.
     * POST /scan/tool/{tool}
     */
    public function checkoutTool(Request $request, Tool $tool): RedirectResponse
    {
        abort_if(! $tool->is_active, 404);

        $validated = $request->validate([
            'borrower_name'  => ['required', 'string', 'max:255'],
            'borrower_email' => ['nullable', 'email', 'max:255'],
            'quantity'       => ['required', 'integer', 'min:1'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ], [
            'borrower_name.required' => 'Please enter your name.',
            'quantity.min'           => 'Quantity must be at least 1.',
        ]);

        // Re-check availability at time of submit (race condition guard)
        $available = $tool->available_quantity;
        if ($validated['quantity'] > $available) {
            return back()->withErrors([
                'quantity' => "Only {$available} unit(s) available right now.",
            ])->withInput();
        }

        $transaction = Transaction::create([
            'user_id'        => 1, // system user
            'room_id'        => null,
            'tool_id'        => $tool->id,
            'quantity'       => $validated['quantity'],
            'borrower_name'  => $validated['borrower_name'],
            'borrower_email' => $validated['borrower_email'] ?? null,
            'subject'        => null,
            'checked_out_at' => now(),
            'status'         => 'open',
            'notes'          => $validated['notes'] ?? null,
            'source'         => 'qr_scan',
        ]);

        $transaction->load('tool');
        $this->telegram->sendCheckoutNotification($transaction);

        Log::info('QR tool checkout', [
            'transaction_id' => $transaction->id,
            'tool'           => $tool->name,
            'borrower'       => $transaction->borrower_name,
            'quantity'       => $transaction->quantity,
        ]);

        return redirect()->route('scan.success', $transaction->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Return flow
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show the return confirmation page.
     * GET /scan/return/{transaction}
     */
    public function showReturn(Transaction $transaction): View
    {
        $transaction->load(['room', 'tool']);

        if ($transaction->status === 'returned') {
            return view('scan.already-returned', compact('transaction'));
        }

        return view('scan.return', compact('transaction'));
    }

    /**
     * Process the return.
     * POST /scan/return/{transaction}
     */
    public function processReturn(Transaction $transaction): RedirectResponse
    {
        if ($transaction->status === 'returned') {
            return redirect()->route('scan.return', $transaction->id);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'returned_at' => now(),
                'status'      => 'returned',
            ]);
        });

        $transaction->refresh()->load(['room', 'tool']);
        $this->telegram->sendReturnNotification($transaction);

        Log::info('QR return processed', [
            'transaction_id' => $transaction->id,
            'borrower'       => $transaction->borrower_name,
        ]);

        return redirect()->route('scan.return-success', $transaction->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Success / confirmation pages
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Checkout success page — shows Transaction ID and return QR link.
     * GET /scan/success/{transaction}
     */
    public function success(Transaction $transaction): View
    {
        $transaction->load(['room', 'tool']);
        return view('scan.success', compact('transaction'));
    }

    /**
     * Return success confirmation page.
     * GET /scan/return-success/{transaction}
     */
    public function returnSuccess(Transaction $transaction): View
    {
        $transaction->load(['room', 'tool']);
        return view('scan.return-success', compact('transaction'));
    }
}
