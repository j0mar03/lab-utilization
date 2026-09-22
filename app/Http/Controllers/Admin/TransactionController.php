<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Tool;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Admin Transaction Controller
 *
 * Displays full transaction history with filters, CSV export,
 * and direct manual checkout & return management for SA and Lab Head.
 * Accessible to: lab_head, student_assistant
 */
class TransactionController extends Controller
{
    public function __construct(private TelegramService $telegram)
    {
    }

    /**
     * Paginated, filterable transaction history.
     * GET /admin/transactions
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['room', 'tool', 'items.tool', 'user'])
            ->latest('checked_out_at');

        // ── Filter: Status ────────────────────────────────────────────────
        if ($request->filled('status') && in_array($request->status, ['open', 'partially_returned', 'returned', 'overdue'])) {
            $query->where('status', $request->status);
        }

        // ── Filter: Room ──────────────────────────────────────────────────
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // ── Filter: Tool ──────────────────────────────────────────────────
        if ($request->filled('tool_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('tool_id', $request->tool_id)
                  ->orWhereHas('items', function ($iq) use ($request) {
                      $iq->where('tool_id', $request->tool_id);
                  });
            });
        }

        // ── Filter: Borrower name search ──────────────────────────────────
        if ($request->filled('borrower')) {
            $query->where('borrower_name', 'like', '%' . $request->borrower . '%');
        }

        // ── Filter: Date range ────────────────────────────────────────────
        if ($request->filled('date_from')) {
            $query->whereDate('checked_out_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('checked_out_at', '<=', $request->date_to);
        }

        // ── Filter: Source ────────────────────────────────────────────────
        if ($request->filled('source') && in_array($request->source, ['google_form', 'qr_scan', 'dashboard'])) {
            $query->where('source', $request->source);
        }

        $transactions = $query->paginate(25)->withQueryString();

        // Dropdown options for filter selects
        $rooms = Room::orderByRaw("CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END, name")->get();
        $tools = Tool::orderBy('category')->orderBy('name')->get();

        // Summary counts for the filter result
        $totalOpen     = (clone $query)->where('status', 'open')->count();
        $totalPartial  = (clone $query)->where('status', 'partially_returned')->count();
        $totalOverdue  = (clone $query)->where('status', 'overdue')->count();
        $totalReturned = (clone $query)->where('status', 'returned')->count();

        return view('admin.transactions.index', compact(
            'transactions', 'rooms', 'tools', 'totalOpen', 'totalPartial', 'totalOverdue', 'totalReturned'
        ));
    }

    /**
     * Show form to manually record a transaction (Room or Tool).
     * GET /admin/transactions/create
     */
    public function create(): View
    {
        $rooms = Room::with(['transactions' => function ($q) {
            $q->whereIn('status', ['open', 'partially_returned'])->latest('checked_out_at');
        }])->orderByRaw("
            CASE 
                WHEN department = 'Department of Computer and Electronics Engineering Technology' THEN 1
                WHEN department = 'Department of Office Management and Information Technology' THEN 2
                WHEN department = 'Department of Electrical and Mechanical Engineering Technology' THEN 3
                ELSE 4
            END,
            CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END,
            name
        ")->get();
        $tools     = Tool::where('is_active', true)->orderBy('department')->orderBy('category')->orderBy('name')->get();
        $faculties = Faculty::where('is_active', true)->orderBy('department')->orderBy('name')->get();
        $subjects  = Subject::where('is_active', true)->orderBy('department')->orderBy('code')->get();
        $softwareCatalog = Transaction::SOFTWARE_CATALOG;

        return view('admin.transactions.create', compact('rooms', 'tools', 'faculties', 'subjects', 'softwareCatalog'));
    }

    /**
     * Store a manually created transaction.
     * POST /admin/transactions
     */
    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'room');

        // Parse Time In and Time Out (supports forgotten sessions & manual logbook entries)
        $timeIn = $request->filled('time_in')
            ? Carbon::parse($request->input('time_in'))
            : now();

        $timeOut = $request->filled('time_out')
            ? Carbon::parse($request->input('time_out'))
            : null;

        if ($timeOut && $timeOut->lt($timeIn)) {
            return back()->withErrors([
                'time_out' => 'Time Out / Return time cannot be earlier than Time In / Borrowed time.'
            ])->withInput();
        }

        $duration = (int) ($request->input('duration_hours', 3));
        $status = $timeOut ? 'returned' : 'open';
        $expectedReturnAt = $timeOut ?: $timeIn->copy()->addHours($duration);

        if ($type === 'room') {
            $validated = $request->validate([
                'borrower_name'       => ['required', 'string', 'max:255'],
                'room_id'             => ['required', 'exists:rooms,id'],
                'subject'             => ['required', 'string', 'max:255'],
                'duration_hours'      => ['nullable', 'integer', 'min:1', 'max:24'],
                'borrower_email'      => ['nullable', 'email', 'max:255'],
                'time_in'             => ['nullable', 'date'],
                'time_out'            => ['nullable', 'date'],
                'notes'               => ['nullable', 'string', 'max:1000'],
                'software_utilized'   => ['nullable', 'array'],
                'software_utilized.*' => ['string', 'max:100'],
                'software_custom'     => ['nullable', 'string', 'max:255'],
            ], [
                'borrower_name.required' => 'Please enter the name of the faculty or borrower.',
                'room_id.required'       => 'Please select the room to use.',
                'subject.required'       => 'Please enter the course subject or purpose.',
            ]);

            $softwareUtilized = (array) $request->input('software_utilized', []);
            if ($request->filled('software_custom')) {
                $customItems = array_filter(array_map('trim', explode(',', $request->input('software_custom'))));
                foreach ($customItems as $cItem) {
                    if (!in_array($cItem, $softwareUtilized)) {
                        $softwareUtilized[] = $cItem;
                    }
                }
            }
            $softwareUtilized = !empty($softwareUtilized) ? array_values(array_unique($softwareUtilized)) : null;

            // ── OPTIONAL TOOLS & ACCESSORIES BORROWED WITH ROOM ───────
            $rawRoomTools = $request->input('room_tools', $request->input('tools', []));
            $roomItemsData = [];

            if (is_array($rawRoomTools) && count($rawRoomTools) > 0) {
                foreach ($rawRoomTools as $t) {
                    if (empty($t['tool_id'])) continue;
                    $roomItemsData[] = [
                        'tool_id'  => (int) $t['tool_id'],
                        'quantity' => max(1, (int) ($t['quantity'] ?? 1)),
                    ];
                }
            }

            // Consolidate duplicates if same tool selected multiple times
            $consolidatedRoomTools = [];
            foreach ($roomItemsData as $item) {
                $tid = $item['tool_id'];
                if (isset($consolidatedRoomTools[$tid])) {
                    $consolidatedRoomTools[$tid]['quantity'] += $item['quantity'];
                } else {
                    $consolidatedRoomTools[$tid] = $item;
                }
            }
            $roomItemsData = array_values($consolidatedRoomTools);

            // Validate tools availability
            foreach ($roomItemsData as $item) {
                $tool = Tool::findOrFail($item['tool_id']);
                if (! $tool->is_active) {
                    return back()->withErrors(['room_tools' => "Tool '{$tool->name}' is currently marked as inactive."])->withInput();
                }

                if (! $timeOut && $item['quantity'] > $tool->available_quantity) {
                    return back()->withErrors([
                        'room_tools' => "Only {$tool->available_quantity} unit(s) available for '{$tool->name}' (requested: {$item['quantity']})."
                    ])->withInput();
                }
            }

            // ── CONFLICT DETECTION: Check if room is already occupied ────
            $activeRoomTx = Transaction::where('room_id', $validated['room_id'])
                ->whereIn('status', ['open', 'partially_returned'])
                ->latest('checked_out_at')
                ->first();

            if ($activeRoomTx && ! $timeOut) {
                $conflictResolution = $request->input('conflict_resolution', 'end_previous');

                if ($conflictResolution === 'end_previous') {
                    // Automatically close out previous active session upon handover
                    $activeRoomTx->update([
                        'status'      => 'returned',
                        'returned_at' => $timeIn,
                        'notes'       => trim(($activeRoomTx->notes ? $activeRoomTx->notes . "\n" : '') . "[Handover: Auto-returned upon handover to {$validated['borrower_name']}]"),
                    ]);

                    // Return any tools checked out with the previous session
                    foreach ($activeRoomTx->items as $prevItem) {
                        if ($prevItem->status !== 'returned') {
                            $prevItem->update([
                                'quantity_returned' => $prevItem->quantity_borrowed,
                                'status'            => 'returned',
                                'returned_at'       => $timeIn,
                            ]);
                        }
                    }

                    $activeRoomTx->load(['room', 'tool', 'items.tool']);
                    $this->telegram->sendReturnNotification($activeRoomTx);
                } elseif ($conflictResolution !== 'allow_concurrent') {
                    return back()->withErrors([
                        'room_id' => "Room '{$activeRoomTx->room?->name}' is currently in use by {$activeRoomTx->borrower_name} (checked out at {$activeRoomTx->checked_out_at->format('M d, g:i A')}). Please select 'End previous session' or 'Allow shared occupancy'."
                    ])->withInput();
                }
            }

            $transaction = Transaction::create([
                'user_id'            => auth()->id(),
                'room_id'            => $validated['room_id'],
                'tool_id'            => null,
                'quantity'           => 1,
                'borrower_name'      => $validated['borrower_name'],
                'borrower_email'     => $validated['borrower_email'] ?? null,
                'department'         => $request->input('department') ?: null,
                'subject'            => $validated['subject'],
                'software_utilized'  => $softwareUtilized,
                'checked_out_at'     => $timeIn,
                'returned_at'        => $timeOut,
                'expected_return_at' => $expectedReturnAt,
                'status'             => $status,
                'notes'              => $validated['notes'] ?? null,
                'source'             => 'dashboard',
            ]);

            // Save each borrowed tool item attached to this room session
            foreach ($roomItemsData as $item) {
                TransactionItem::create([
                    'transaction_id'    => $transaction->id,
                    'tool_id'           => $item['tool_id'],
                    'quantity_borrowed' => $item['quantity'],
                    'quantity_returned' => $timeOut ? $item['quantity'] : 0,
                    'status'            => $timeOut ? 'returned' : 'borrowed',
                    'returned_at'       => $timeOut,
                ]);
            }
        } else {
            // ── MULTI-TOOL & SINGLE-TOOL BORROWING ────────────────────────────
            $rawTools = $request->input('tools');
            $itemsData = [];

            if (is_array($rawTools) && count($rawTools) > 0) {
                foreach ($rawTools as $t) {
                    if (empty($t['tool_id'])) continue;
                    $itemsData[] = [
                        'tool_id'  => (int) $t['tool_id'],
                        'quantity' => max(1, (int) ($t['quantity'] ?? 1)),
                    ];
                }
            } elseif ($request->filled('tool_id')) {
                $itemsData[] = [
                    'tool_id'  => (int) $request->input('tool_id'),
                    'quantity' => max(1, (int) $request->input('quantity', 1)),
                ];
            }

            if (empty($itemsData)) {
                return back()->withErrors(['tool_id' => 'Please select at least one tool to borrow.'])->withInput();
            }

            // Consolidate duplicate tools if selected multiple times
            $consolidated = [];
            foreach ($itemsData as $item) {
                $tid = $item['tool_id'];
                if (isset($consolidated[$tid])) {
                    $consolidated[$tid]['quantity'] += $item['quantity'];
                } else {
                    $consolidated[$tid] = $item;
                }
            }
            $itemsData = array_values($consolidated);

            $totalQuantity = 0;
            $primaryToolId = $itemsData[0]['tool_id'];

            foreach ($itemsData as $item) {
                $tool = Tool::findOrFail($item['tool_id']);
                if (! $tool->is_active) {
                    return back()->withErrors(['tool_id' => "Tool '{$tool->name}' is currently marked as inactive."])->withInput();
                }

                // Check stock for active transactions
                if (! $timeOut && $item['quantity'] > $tool->available_quantity) {
                    return back()->withErrors([
                        'tool_id' => "Only {$tool->available_quantity} unit(s) available for '{$tool->name}' (requested: {$item['quantity']})."
                    ])->withInput();
                }

                $totalQuantity += $item['quantity'];
            }

            $validated = $request->validate([
                'borrower_name'  => ['required', 'string', 'max:255'],
                'subject'        => ['nullable', 'string', 'max:255'],
                'duration_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
                'borrower_email' => ['nullable', 'email', 'max:255'],
                'time_in'        => ['nullable', 'date'],
                'time_out'       => ['nullable', 'date'],
                'notes'          => ['nullable', 'string', 'max:1000'],
            ], [
                'borrower_name.required' => 'Please enter the name of the borrower.',
            ]);

            $transaction = Transaction::create([
                'user_id'            => auth()->id(),
                'room_id'            => null,
                'tool_id'            => $primaryToolId,
                'quantity'           => $totalQuantity,
                'borrower_name'      => $validated['borrower_name'],
                'borrower_email'     => $validated['borrower_email'] ?? null,
                'department'         => $request->input('department') ?: null,
                'subject'            => $request->input('tool_subject') ?: ($validated['subject'] ?? null),
                'checked_out_at'     => $timeIn,
                'returned_at'        => $timeOut,
                'expected_return_at' => $expectedReturnAt,
                'status'             => $status,
                'notes'              => $validated['notes'] ?? null,
                'source'             => 'dashboard',
            ]);

            // Record each borrowed tool item
            foreach ($itemsData as $item) {
                TransactionItem::create([
                    'transaction_id'    => $transaction->id,
                    'tool_id'           => $item['tool_id'],
                    'quantity_borrowed' => $item['quantity'],
                    'quantity_returned' => $timeOut ? $item['quantity'] : 0,
                    'status'            => $timeOut ? 'returned' : 'borrowed',
                    'returned_at'       => $timeOut,
                ]);
            }
        }

        $transaction->load(['room', 'tool', 'items.tool']);

        $itemCount = $transaction->items->count();
        if ($status === 'open') {
            $this->telegram->sendCheckoutNotification($transaction);
            if ($type === 'room' && $itemCount > 0) {
                $msg = "Transaction #{$transaction->id} created successfully for Room {$transaction->room?->name} with {$itemCount} accessory/tool item(s) checked out.";
            } else {
                $msg = "Transaction #{$transaction->id} created successfully (Active / In-Use).";
            }
        } else {
            if ($type === 'room' && $itemCount > 0) {
                $msg = "Transaction #{$transaction->id} recorded into logbook as Completed (Room {$transaction->room?->name} and {$itemCount} accessory item(s) returned at {$timeOut->format('M d, Y h:i A')}).";
            } else {
                $msg = "Transaction #{$transaction->id} recorded into logbook as Completed (Returned at {$timeOut->format('M d, Y h:i A')}).";
            }
        }

        return redirect()->route('admin.transactions.index')
            ->with('success', $msg);
    }

    /**
     * Mark an open, overdue, or partially returned transaction as returned.
     * Supports returning partial quantities or all remaining items.
     * POST /admin/transactions/{transaction}/return
     */
    public function markReturned(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->status === 'returned') {
            return back()->with('info', 'This transaction is already marked as fully returned.');
        }

        // Room transaction: Instant 1-click return
        if ($transaction->room_id && ! $transaction->items()->exists() && ! $transaction->tool_id) {
            $transaction->update([
                'status'      => 'returned',
                'returned_at' => now(),
            ]);

            $transaction->load(['room', 'tool']);
            $this->telegram->sendReturnNotification($transaction);

            return back()->with('success', "Room utilization #{$transaction->id} ({$transaction->room?->name}) has been marked as returned.");
        }

        // Tool transaction: multi-tool and partial return support
        $transaction->load(['items.tool', 'tool']);
        $returnItemsInput = $request->input('return_items'); // [ item_id => quantity_returning_now ]
        $returnNotes = $request->input('return_notes');

        if ($transaction->items->isNotEmpty()) {
            $totalNewlyReturned = 0;

            foreach ($transaction->items as $item) {
                if ($item->status === 'returned') {
                    continue;
                }

                if (is_array($returnItemsInput) && array_key_exists($item->id, $returnItemsInput)) {
                    $qtyToReturnNow = max(0, (int) $returnItemsInput[$item->id]);
                } else {
                    // Default quick return: return all remaining for this item
                    $qtyToReturnNow = $item->remaining_quantity;
                }

                if ($qtyToReturnNow > 0) {
                    $newQtyReturned = min($item->quantity_borrowed, $item->quantity_returned + $qtyToReturnNow);
                    $itemStatus = ($newQtyReturned >= $item->quantity_borrowed) ? 'returned' : 'partially_returned';

                    $item->update([
                        'quantity_returned' => $newQtyReturned,
                        'status'            => $itemStatus,
                        'returned_at'       => ($itemStatus === 'returned') ? now() : $item->returned_at,
                        'notes'             => $returnNotes ?: $item->notes,
                    ]);

                    $totalNewlyReturned += $qtyToReturnNow;
                }
            }

            $transaction->refresh();
            $transaction->load('items.tool');

            $allReturned = $transaction->items->every(fn($i) => $i->status === 'returned');
            $anyReturned = $transaction->items->some(fn($i) => $i->quantity_returned > 0);

            if ($allReturned) {
                $transaction->update([
                    'status'      => 'returned',
                    'returned_at' => now(),
                ]);
                if ($transaction->room_id) {
                    $msg = "Transaction #{$transaction->id} ({$transaction->borrower_name}) — Room {$transaction->room?->name} and all borrowed tools/accessories have been fully returned.";
                } else {
                    $msg = "Transaction #{$transaction->id} ({$transaction->borrower_name}) — All tools have been fully returned.";
                }
            } elseif ($anyReturned) {
                $transaction->update([
                    'status' => 'partially_returned',
                ]);
                $msg = "Transaction #{$transaction->id} ({$transaction->borrower_name}) — Partial return recorded ({$totalNewlyReturned} unit(s) returned). Remaining tools remain borrowed.";
            } else {
                return back()->with('warning', 'No tool quantities were specified to return.');
            }
        } else {
            // Legacy single-tool transaction without items table
            $transaction->update([
                'status'      => 'returned',
                'returned_at' => now(),
            ]);
            $msg = "Transaction #{$transaction->id} ({$transaction->borrower_name}) has been marked as returned.";
        }

        $transaction->load(['room', 'tool', 'items.tool']);
        $this->telegram->sendReturnNotification($transaction);

        return back()->with('success', $msg);
    }

    /**
     * Show form to edit an existing transaction (fix wrong room, borrower, software, dates, status).
     * GET /admin/transactions/{transaction}/edit
     */
    public function edit(Transaction $transaction): View
    {
        $transaction->load(['room', 'tool', 'items.tool']);

        $rooms = Room::orderByRaw("
            CASE 
                WHEN department = 'Department of Computer and Electronics Engineering Technology' THEN 1
                WHEN department = 'Department of Office Management and Information Technology' THEN 2
                WHEN department = 'Department of Electrical and Mechanical Engineering Technology' THEN 3
                ELSE 4
            END,
            CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END,
            name
        ")->get();

        $tools           = Tool::where('is_active', true)->orderBy('department')->orderBy('category')->orderBy('name')->get();
        $faculties       = Faculty::where('is_active', true)->orderBy('department')->orderBy('name')->get();
        $subjects        = Subject::where('is_active', true)->orderBy('department')->orderBy('code')->get();
        $softwareCatalog = Transaction::SOFTWARE_CATALOG;

        return view('admin.transactions.edit', compact('transaction', 'rooms', 'tools', 'faculties', 'subjects', 'softwareCatalog'));
    }

    /**
     * Update an existing transaction.
     * PUT /admin/transactions/{transaction}
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $isRoom = $transaction->isRoom() || $request->filled('room_id');

        if ($isRoom) {
            $validated = $request->validate([
                'room_id'             => ['required', 'exists:rooms,id'],
                'borrower_name'       => ['required', 'string', 'max:255'],
                'borrower_email'      => ['nullable', 'email', 'max:255'],
                'department'          => ['nullable', 'string', 'max:255'],
                'subject'             => ['required', 'string', 'max:255'],
                'software_utilized'   => ['nullable', 'array'],
                'software_utilized.*' => ['string', 'max:100'],
                'software_custom'     => ['nullable', 'string', 'max:255'],
                'checked_out_at'      => ['required', 'date'],
                'expected_return_at'  => ['nullable', 'date'],
                'returned_at'         => ['nullable', 'date'],
                'status'              => ['required', 'in:open,partially_returned,returned,overdue'],
                'notes'               => ['nullable', 'string', 'max:1000'],
            ]);

            $softwareUtilized = (array) $request->input('software_utilized', []);
            if ($request->filled('software_custom')) {
                $customItems = array_filter(array_map('trim', explode(',', $request->input('software_custom'))));
                foreach ($customItems as $cItem) {
                    if (!in_array($cItem, $softwareUtilized)) {
                        $softwareUtilized[] = $cItem;
                    }
                }
            }
            $softwareUtilized = !empty($softwareUtilized) ? array_values(array_unique($softwareUtilized)) : null;

            $timeIn   = Carbon::parse($validated['checked_out_at']);
            $timeOut  = $request->filled('returned_at') ? Carbon::parse($request->input('returned_at')) : null;
            $expected = $request->filled('expected_return_at') ? Carbon::parse($request->input('expected_return_at')) : null;

            $status = $validated['status'];
            if ($status === 'returned' && ! $timeOut) {
                $timeOut = now();
            } elseif ($status === 'open' && ! $request->filled('returned_at')) {
                $timeOut = null;
            }

            // If department wasn't explicitly chosen, resolve it
            $department = $validated['department'] ?: Transaction::resolveDepartmentFor(
                $validated['room_id'],
                null,
                $validated['borrower_name'],
                $validated['subject']
            );

            $oldRoomName = $transaction->room?->name ?? 'None';

            $transaction->update([
                'room_id'            => $validated['room_id'],
                'tool_id'            => null,
                'borrower_name'      => $validated['borrower_name'],
                'borrower_email'     => $validated['borrower_email'] ?? null,
                'department'         => $department,
                'subject'            => $validated['subject'],
                'software_utilized'  => $softwareUtilized,
                'checked_out_at'     => $timeIn,
                'expected_return_at' => $expected,
                'returned_at'        => $timeOut,
                'status'             => $status,
                'notes'              => $validated['notes'] ?? null,
            ]);

            $transaction->refresh()->load(['room', 'items']);

            if ($status === 'returned') {
                foreach ($transaction->items as $item) {
                    if ($item->status !== 'returned') {
                        $item->update([
                            'quantity_returned' => $item->quantity_borrowed,
                            'status'            => 'returned',
                            'returned_at'       => $timeOut ?: now(),
                        ]);
                    }
                }
            }

            $newRoomName = $transaction->room?->name ?? 'None';

            $msg = "Transaction #{$transaction->id} updated successfully.";
            if ($oldRoomName !== $newRoomName) {
                $msg .= " Room changed from {$oldRoomName} to {$newRoomName}.";
            }

            return redirect()->route('admin.transactions.show', $transaction)
                ->with('success', $msg);
        } else {
            // Tool transaction update
            $validated = $request->validate([
                'tool_id'            => ['required', 'exists:tools,id'],
                'quantity'           => ['required', 'integer', 'min:1'],
                'borrower_name'      => ['required', 'string', 'max:255'],
                'borrower_email'     => ['nullable', 'email', 'max:255'],
                'department'         => ['nullable', 'string', 'max:255'],
                'subject'            => ['nullable', 'string', 'max:255'],
                'checked_out_at'     => ['required', 'date'],
                'expected_return_at' => ['nullable', 'date'],
                'returned_at'        => ['nullable', 'date'],
                'status'             => ['required', 'in:open,partially_returned,returned,overdue'],
                'notes'              => ['nullable', 'string', 'max:1000'],
            ]);

            $timeIn   = Carbon::parse($validated['checked_out_at']);
            $timeOut  = $request->filled('returned_at') ? Carbon::parse($request->input('returned_at')) : null;
            $expected = $request->filled('expected_return_at') ? Carbon::parse($request->input('expected_return_at')) : null;

            $status = $validated['status'];
            if ($status === 'returned' && ! $timeOut) {
                $timeOut = now();
            } elseif ($status === 'open' && ! $request->filled('returned_at')) {
                $timeOut = null;
            }

            $department = $validated['department'] ?: Transaction::resolveDepartmentFor(
                null,
                $validated['tool_id'],
                $validated['borrower_name'],
                $validated['subject']
            );

            $transaction->update([
                'tool_id'            => $validated['tool_id'],
                'quantity'           => $validated['quantity'],
                'borrower_name'      => $validated['borrower_name'],
                'borrower_email'     => $validated['borrower_email'] ?? null,
                'department'         => $department,
                'subject'            => $validated['subject'] ?? null,
                'checked_out_at'     => $timeIn,
                'expected_return_at' => $expected,
                'returned_at'        => $timeOut,
                'status'             => $status,
                'notes'              => $validated['notes'] ?? null,
            ]);

            return redirect()->route('admin.transactions.show', $transaction)
                ->with('success', "Transaction #{$transaction->id} updated successfully.");
        }
    }

    /**
     * Delete an invalid or test transaction.
     * DELETE /admin/transactions/{transaction}
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $id   = $transaction->id;
        $name = $transaction->borrower_name;

        // Delete any related transaction items or notification logs
        $transaction->items()->delete();
        $transaction->notificationLogs()->delete();
        $transaction->delete();

        return redirect()->route('admin.transactions.index')
            ->with('success', "Transaction #{$id} ({$name}) has been deleted.");
    }

    /**
     * Show a single transaction's full details.
     * GET /admin/transactions/{transaction}
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['room', 'tool', 'items.tool', 'user', 'notificationLogs']);
        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Export filtered transactions to CSV.
     * GET /admin/transactions/export
     */
    public function export(Request $request): Response
    {
        $query = Transaction::with(['room', 'tool'])
            ->latest('checked_out_at');

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('room_id'))   $query->where('room_id', $request->room_id);
        if ($request->filled('tool_id'))   $query->where('tool_id', $request->tool_id);
        if ($request->filled('borrower'))  $query->where('borrower_name', 'like', '%' . $request->borrower . '%');
        if ($request->filled('date_from')) $query->whereDate('checked_out_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('checked_out_at', '<=', $request->date_to);

        $transactions = $query->get();

        $filename = 'lab-transactions-' . now()->format('Y-m-d') . '.csv';

        $csvLines = [];
        $csvLines[] = implode(',', [
            'Transaction ID',
            'Borrower Name',
            'Borrower Email',
            'Department',
            'Room',
            'Tool',
            'Quantity',
            'Subject',
            'Software Utilized',
            'Checked Out At',
            'Expected Return',
            'Returned At',
            'Status',
            'Source',
            'Notes',
        ]);

        foreach ($transactions as $tx) {
            $csvLines[] = implode(',', array_map(
                fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                [
                    $tx->id,
                    $tx->borrower_name,
                    $tx->borrower_email ?? '',
                    $tx->departmentShort() ?: ($tx->department ?? ''),
                    $tx->room?->name ?? '',
                    $tx->tool?->name ?? '',
                    $tx->quantity,
                    $tx->subject ?? '',
                    $tx->softwareSummary(),
                    $tx->checked_out_at?->format('Y-m-d H:i:s') ?? '',
                    $tx->expected_return_at?->format('Y-m-d H:i:s') ?? '',
                    $tx->returned_at?->format('Y-m-d H:i:s') ?? '',
                    $tx->status,
                    $tx->source,
                    $tx->notes ?? '',
                ]
            ));
        }

        return response(implode("\n", $csvLines), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
