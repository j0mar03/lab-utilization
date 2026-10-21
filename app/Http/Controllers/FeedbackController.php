<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Room;
use App\Models\Transaction;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function __construct(private TelegramService $telegram)
    {
    }

    /**
     * Show the feedback survey form.
     * Accessible via:
     *   /feedback
     *   /feedback/room/{room}
     *   /feedback?transaction_id=123&room_id=4
     */
    public function create(Request $request, ?Room $room = null): View
    {
        $rooms = Room::orderByRaw("CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END, name")->get();

        $selectedRoom = $room;
        $transaction = null;

        if ($request->filled('transaction_id')) {
            $transaction = Transaction::with(['room', 'user'])->find($request->input('transaction_id'));
            if ($transaction && $transaction->room && !$selectedRoom) {
                $selectedRoom = $transaction->room;
            }
        } elseif ($request->filled('room_id') && !$selectedRoom) {
            $selectedRoom = Room::find($request->input('room_id'));
        }

        $defaultCapacity = 'Student';
        $defaultName = '';
        $defaultEmail = '';

        if ($transaction) {
            $defaultCapacity = $transaction->borrower_type === 'faculty' ? 'Faculty' : 'Student';
            $defaultName = $transaction->borrower_name;
            $defaultEmail = $transaction->borrower_email;
        }

        return view('feedback.create', [
            'rooms'           => $rooms,
            'selectedRoom'    => $selectedRoom,
            'transaction'     => $transaction,
            'defaultCapacity' => $defaultCapacity,
            'defaultName'     => $defaultName,
            'defaultEmail'    => $defaultEmail,
            'issueLabels'     => Feedback::issueLabels(),
        ]);
    }

    /**
     * Store the feedback response.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id'              => ['required', 'exists:rooms,id'],
            'transaction_id'       => ['nullable', 'exists:transactions,id'],
            'capacity'             => ['required', 'in:Student,Faculty,Guest'],
            'respondent_name'      => ['nullable', 'string', 'max:120'],
            'respondent_email'     => ['nullable', 'email', 'max:150'],
            'q1_environment'       => ['required', 'integer', 'between:1,5'],
            'q2_resources'         => ['required', 'integer', 'between:1,5'],
            'q3_safety'            => ['required', 'integer', 'between:1,5'],
            'q4_service'           => ['required', 'integer', 'between:1,5'],
            'q5_system_experience' => ['required', 'integer', 'between:1,5'],
            'overall_score'        => ['required', 'integer', 'between:1,5'],
            'reported_issues'      => ['nullable', 'array'],
            'reported_issues.*'    => ['string', 'in:projector,pc_software,aircon_ventilation,cleanliness,missing_tools,none'],
            'suggestions'          => ['nullable', 'string', 'max:2000'],
        ]);

        // Clean reported issues
        $issues = $validated['reported_issues'] ?? [];
        if (in_array('none', $issues)) {
            $issues = ['none'];
        }

        $feedback = Feedback::create([
            'room_id'              => $validated['room_id'],
            'transaction_id'       => $validated['transaction_id'] ?? null,
            'capacity'             => $validated['capacity'],
            'respondent_name'      => $validated['respondent_name'] ?? null,
            'respondent_email'     => $validated['respondent_email'] ?? null,
            'q1_environment'       => (int) $validated['q1_environment'],
            'q2_resources'         => (int) $validated['q2_resources'],
            'q3_safety'            => (int) $validated['q3_safety'],
            'q4_service'           => (int) $validated['q4_service'],
            'q5_system_experience' => (int) $validated['q5_system_experience'],
            'overall_score'        => (int) $validated['overall_score'],
            'reported_issues'      => $issues,
            'suggestions'          => $validated['suggestions'] ?? null,
            'ip_address'           => $request->ip(),
            'user_agent'           => substr((string) $request->userAgent(), 0, 500),
        ]);

        // Send Telegram notification
        try {
            $this->telegram->sendFeedbackNotification($feedback);
        } catch (\Throwable $e) {
            // Keep going even if Telegram is unconfigured or offline
        }

        return redirect()->route('feedback.success', $feedback->id);
    }

    /**
     * Show feedback success appreciation page.
     */
    public function success(Feedback $feedback): View
    {
        $feedback->loadMissing(['room', 'transaction']);
        return view('feedback.success', compact('feedback'));
    }
}
