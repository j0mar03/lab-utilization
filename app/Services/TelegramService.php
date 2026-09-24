<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TelegramService
 *
 * Sends formatted utilization log notifications to a Telegram chat/group via Bot API.
 * All bot credentials are read from config/telegram.php (which reads .env).
 * No tokens are hardcoded here.
 *
 * Usage:
 *   app(TelegramService::class)->sendCheckoutNotification($transaction);
 *   app(TelegramService::class)->sendReturnNotification($transaction);
 *   app(TelegramService::class)->sendOverdueAlert($transaction);
 *   app(TelegramService::class)->sendRawMessage('Hello, lab!', $chatId);
 */
class TelegramService
{
    private string $botToken;
    private string $apiUrl;
    private ?string $defaultChatId;

    public function __construct()
    {
        $this->botToken      = config('telegram.bot_token', '');
        $this->apiUrl        = config('telegram.api_url', 'https://api.telegram.org/bot');
        $this->defaultChatId = config('telegram.default_chat_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public notification methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Send a checkout notification to the lab Telegram group.
     */
    public function sendCheckoutNotification(Transaction $transaction): bool
    {
        $transaction->loadMissing(['room', 'tool', 'items.tool']);

        $isRoom = $transaction->isRoom();
        $icon   = $isRoom ? '🏫' : '🔧';
        $title  = $isRoom ? 'LAB ROOM CHECKOUT' : 'EQUIPMENT BORROWED';
        $dept   = $transaction->departmentShort() ?: 'General / Shared';
        $time   = $transaction->checked_out_at ? $transaction->checked_out_at->format('M d, Y g:i A') : now()->format('M d, Y g:i A');

        $message  = "{$icon} *{$title}*\n";
        $message .= "─────────────────────────\n";
        $message .= "👤 *Borrower:* {$transaction->borrower_name}\n";
        $message .= "🏢 *Department:* {$dept}\n";

        if ($isRoom) {
            $message .= "🚪 *Room:* " . ($transaction->room?->name ?? 'Room') . "\n";
            if ($transaction->hasSoftwareUtilized()) {
                $message .= "💻 *Software Utilized:* " . $transaction->softwareSummary() . "\n";
            }
            if ($transaction->items && $transaction->items->isNotEmpty()) {
                $itemList = $transaction->items->map(function ($item) {
                    $name = $item->tool?->name ?? 'Tool';
                    return "   • {$name} (×{$item->quantity_borrowed})";
                })->join("\n");
                $message .= "📦 *Accessories / Equipment Borrowed:*\n{$itemList}\n";
            }
        } else {
            if ($transaction->items && $transaction->items->isNotEmpty()) {
                $itemList = $transaction->items->map(function ($item) {
                    $name = $item->tool?->name ?? 'Tool';
                    return "   • {$name} (×{$item->quantity_borrowed})";
                })->join("\n");
                $message .= "📦 *Equipment Items:*\n{$itemList}\n";
            } elseif ($transaction->tool) {
                $qty = $transaction->quantity > 1 ? " (×{$transaction->quantity})" : '';
                $message .= "📦 *Equipment:* {$transaction->tool->name}{$qty}\n";
            }
        }

        if ($transaction->subject) {
            $message .= "📚 *Purpose / Subject:* {$transaction->subject}\n";
        }

        $message .= "🕐 *Time Out:* {$time}\n";

        if ($transaction->expected_return_at) {
            $returnTime = $transaction->expected_return_at->format('g:i A');
            $message .= "⏰ *Expected Return:* {$returnTime}\n";
        }

        if ($transaction->notes) {
            $message .= "📝 *Notes:* {$transaction->notes}\n";
        }

        return $this->send($message, $this->defaultChatId, $transaction, 'telegram');
    }

    /**
     * Send a return notification to the lab Telegram group.
     */
    public function sendReturnNotification(Transaction $transaction): bool
    {
        $transaction->loadMissing(['room', 'tool', 'items.tool']);

        $isRoom     = $transaction->isRoom();
        $isVacated  = ($transaction->status === 'returned');

        if ($isRoom && ! $isVacated) {
            $icon  = '🔧';
            $title = 'ACCESSORIES RETURNED (ROOM IN USE)';
        } elseif ($isRoom) {
            $icon  = '🏫';
            $title = 'ROOM VACATED';
        } else {
            $icon  = ($transaction->status === 'partially_returned') ? '⚡' : '✅';
            $title = ($transaction->status === 'partially_returned') ? 'PARTIAL RETURN' : 'ITEM RETURNED';
        }
        $dept       = $transaction->departmentShort() ?: 'General / Shared';
        $returnTime = $transaction->returned_at ? $transaction->returned_at->format('M d, Y g:i A') : now()->format('M d, Y g:i A');

        $message  = "{$icon} *{$title}*\n";
        $message .= "─────────────────────────\n";
        $message .= "👤 *Borrower:* {$transaction->borrower_name}\n";
        $message .= "🏢 *Department:* {$dept}\n";

        if ($isRoom) {
            $roomStatus = $isVacated ? 'Vacated' : 'Still actively in use';
            $message .= "🚪 *Room:* " . ($transaction->room?->name ?? 'Room') . " ({$roomStatus})\n";
            if ($transaction->hasSoftwareUtilized()) {
                $message .= "💻 *Software:* " . $transaction->softwareSummary() . "\n";
            }
            if ($transaction->items && $transaction->items->isNotEmpty()) {
                $itemList = $transaction->items->map(function ($item) {
                    $name = $item->tool?->name ?? 'Tool';
                    $ret = $item->quantity_returned;
                    $borrowed = $item->quantity_borrowed;
                    $statusIcon = $item->status === 'returned' ? '✓' : '⏳';
                    return "   • {$name} ({$ret}/{$borrowed} returned) {$statusIcon}";
                })->join("\n");
                $message .= "📦 *Accessories / Tools:*\n{$itemList}\n";
            }
        } else {
            $message .= "📦 *Item:* " . $transaction->subjectDescription() . "\n";
        }

        $message .= "🕐 *Returned At:* {$returnTime}\n";

        if ($transaction->status === 'partially_returned') {
            $message .= "⚠️ *Status:* Partially Returned (some items still checked out)\n";
        } elseif ($isRoom && ! $isVacated) {
            $message .= "ℹ️ *Status:* Room is still actively occupied\n";
        }

        // Flag if it was overdue before return
        if ($transaction->expected_return_at && $transaction->returned_at && $transaction->returned_at->gt($transaction->expected_return_at)) {
            $diff = $transaction->returned_at->diffForHumans($transaction->expected_return_at, true);
            $message .= "⚠️ *Note:* Returned {$diff} after expected return time.\n";
        }

        return $this->send($message, $this->defaultChatId, $transaction, 'telegram');
    }

    /**
     * Send an overdue alert to the lab Telegram group.
     */
    public function sendOverdueAlert(Transaction $transaction): bool
    {
        $transaction->loadMissing(['room', 'tool', 'items.tool']);

        $dept  = $transaction->departmentShort() ?: 'General / Shared';
        $since = $transaction->expected_return_at ? $transaction->expected_return_at->diffForHumans() : 'now';

        $message  = "🚨 *OVERDUE ALERT*\n";
        $message .= "─────────────────────────\n";
        $message .= "👤 *Borrower:* {$transaction->borrower_name}\n";
        $message .= "🏢 *Department:* {$dept}\n";
        $message .= "📦 *Item:* " . $transaction->subjectDescription() . "\n";

        if ($transaction->expected_return_at) {
            $message .= "⏰ *Was Due:* " . $transaction->expected_return_at->format('M d, g:i A') . " ({$since})\n";
        }

        $message .= "📌 *Action:* Please follow up with the borrower.\n";

        return $this->send($message, $this->defaultChatId, $transaction, 'telegram');
    }

    /**
     * Send a raw text message to a specific chat.
     * Useful for testing or custom one-off notifications.
     */
    public function sendRawMessage(string $message, ?string $chatId = null): bool
    {
        return $this->send($message, $chatId ?? $this->defaultChatId);
    }

    /**
     * Test connection to Telegram bot.
     * Returns array with success status and details.
     */
    public function testConnection(?string $chatId = null): array
    {
        $targetChatId = $chatId ?: $this->defaultChatId;

        if (empty($this->botToken)) {
            return [
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN is not configured in .env',
            ];
        }

        if (empty($targetChatId)) {
            return [
                'success' => false,
                'message' => 'TELEGRAM_DEFAULT_CHAT_ID is not configured in .env',
            ];
        }

        $testMsg = "🤖 *Lab System Telegram Test*\n"
                 . "─────────────────────────\n"
                 . "✅ Bot connection verified successfully!\n"
                 . "🕐 Timestamp: " . now()->format('Y-m-d H:i:s') . "\n"
                 . "🏫 PUP-ITECH Lab Utilization System is online.";

        try {
            $response = Http::timeout(10)
                ->retry(3, 1000)
                ->post("{$this->apiUrl}{$this->botToken}/sendMessage", [
                    'chat_id'    => $targetChatId,
                    'text'       => $testMsg,
                    'parse_mode' => 'Markdown',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Test message sent successfully to Telegram chat ' . $targetChatId,
                ];
            }

            return [
                'success' => false,
                'message' => 'Telegram API error: ' . $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build a formatted markdown summary message for room, tool, and software utilization.
     */
     public function buildUtilizationSummaryMessage(
        \Carbon\CarbonInterface $startDate,
        \Carbon\CarbonInterface $endDate,
        ?string $title = null
    ): string {
        $txs = Transaction::whereBetween('checked_out_at', [$startDate, $endDate])
            ->with(['room', 'tool', 'items.tool'])
            ->get();

        $roomTxs = $txs->whereNotNull('room_id');

        $isSameDay = $startDate->isSameDay($endDate);
        $dateStr   = $isSameDay
            ? $startDate->format('l, M d, Y')
            : $startDate->format('M d, Y') . ' — ' . $endDate->format('M d, Y');

        $headerTitle = $title ?: ($isSameDay ? 'DAILY LAB UTILIZATION SUMMARY' : 'LAB UTILIZATION PERIOD SUMMARY');

        // Total Counts
        $totalSessions = $txs->count();
        $returnedCount = $txs->where('status', 'returned')->count();
        $activeCount   = $txs->whereIn('status', ['open', 'partially_returned', 'overdue'])->count();

        // ── 1. ROOMS ──────────────────────────────────────────────────────────
        $roomCounts = [];
        $totalRoomMinutes = 0;
        foreach ($roomTxs as $tx) {
            $rName = $tx->room?->name ?? ('Room #' . $tx->room_id);
            $roomCounts[$rName] = ($roomCounts[$rName] ?? 0) + 1;

            if ($tx->checked_out_at) {
                $end = $tx->returned_at ?: now();
                $totalRoomMinutes += max(1, (int) $tx->checked_out_at->diffInMinutes($end));
            }
        }
        arsort($roomCounts);
        $totalRoomHours = round($totalRoomMinutes / 60, 1);
        $currentlyOccupied = Room::occupied()->get();

        // ── 2. TOOLS ──────────────────────────────────────────────────────────
        $toolUnitsBorrowed = 0;
        $toolCounts = [];
        foreach ($txs as $tx) {
            if ($tx->items && $tx->items->isNotEmpty()) {
                foreach ($tx->items as $item) {
                    $tName = $item->tool?->name ?? 'Tool';
                    $qty = $item->quantity_borrowed ?: 1;
                    $toolUnitsBorrowed += $qty;
                    $toolCounts[$tName] = ($toolCounts[$tName] ?? 0) + $qty;
                }
            } elseif ($tx->tool) {
                $tName = $tx->tool->name;
                $qty = $tx->quantity ?: 1;
                $toolUnitsBorrowed += $qty;
                $toolCounts[$tName] = ($toolCounts[$tName] ?? 0) + $qty;
            }
        }
        arsort($toolCounts);

        $activeToolsCount = Transaction::whereIn('status', ['open', 'partially_returned', 'overdue'])
            ->where(function ($q) {
                $q->whereNotNull('tool_id')->orWhereHas('items');
            })->count();
        $overdueCount = Transaction::where('status', 'overdue')->count();

        // ── 3. SOFTWARE ───────────────────────────────────────────────────────
        $compLabSessions = 0;
        $swCounts = [];
        foreach ($roomTxs as $tx) {
            if ($tx->hasSoftwareUtilized()) {
                $compLabSessions++;
                foreach ((array) $tx->software_utilized as $sw) {
                    $swCounts[$sw] = ($swCounts[$sw] ?? 0) + 1;
                }
            }
        }
        arsort($swCounts);

        // ── Build Message ─────────────────────────────────────────────────────
        $msg  = "📊 *{$headerTitle}*\n";
        $msg .= "📅 *Date:* {$dateStr}\n";
        $msg .= "─────────────────────────\n";
        $msg .= "👥 *Total Activity:* {$totalSessions} transactions ({$returnedCount} closed, {$activeCount} active)\n\n";

        // Room Section
        $msg .= "🏫 *ROOMS UTILIZATION:*\n";
        $msg .= "• Total Sessions: *{$roomTxs->count()}* (~{$totalRoomHours} hrs)\n";
        if (!empty($roomCounts)) {
            $msg .= "• Top Utilized Rooms:\n";
            $topRooms = array_slice($roomCounts, 0, 4, true);
            foreach ($topRooms as $rName => $count) {
                $msg .= "   - {$rName}: {$count} session" . ($count === 1 ? '' : 's') . "\n";
            }
        } else {
            $msg .= "• Top Rooms: None recorded\n";
        }
        $occCount = $currentlyOccupied->count();
        if ($occCount > 0) {
            $occList = $currentlyOccupied->pluck('name')->join(', ');
            $msg .= "• Active Occupancy: *{$occCount} room(s) [{$occList}]*\n\n";
        } else {
            $msg .= "• Active Occupancy: *All rooms vacant 🟢*\n\n";
        }

        // Tool Section
        $msg .= "🔧 *TOOLS & EQUIPMENT:*\n";
        $msg .= "• Units Borrowed: *{$toolUnitsBorrowed}*\n";
        if (!empty($toolCounts)) {
            $msg .= "• Most Borrowed Equipment:\n";
            $topTools = array_slice($toolCounts, 0, 4, true);
            foreach ($topTools as $tName => $qty) {
                $msg .= "   - {$tName} (×{$qty})\n";
            }
        } else {
            $msg .= "• Most Borrowed: None recorded\n";
        }
        $msg .= "• Active Loans in Field: *{$activeToolsCount}*\n";
        $msg .= "• Overdue Alerts: *" . ($overdueCount > 0 ? "🚨 {$overdueCount} overdue!" : "0 overdue 🟢") . "*\n\n";

        // Software Section
        $msg .= "💻 *SOFTWARE & APPLICATIONS:*\n";
        $msg .= "• Computer Lab Sessions: *{$compLabSessions}*\n";
        if (!empty($swCounts)) {
            $msg .= "• Top Software Utilized:\n";
            $topSw = array_slice($swCounts, 0, 5, true);
            foreach ($topSw as $swName => $count) {
                $msg .= "   - {$swName}: {$count} session" . ($count === 1 ? '' : 's') . "\n";
            }
        } else {
            $msg .= "• Software Utilized: None recorded in this period\n";
        }

        $msg .= "─────────────────────────\n";
        $msg .= "🏛️ *PUP Institute of Technology (ITECH)*\n";
        $msg .= "🕒 _Report Generated: " . now()->format('M d, Y g:i A') . "_";

        return $msg;
    }

    /**
     * Send the utilization summary message to a Telegram chat.
     */
    public function sendUtilizationSummary(
        \Carbon\CarbonInterface $startDate,
        \Carbon\CarbonInterface $endDate,
        ?string $chatId = null,
        ?string $title = null
    ): array {
        $targetChatId = $chatId ?: $this->defaultChatId;

        if (empty($this->botToken)) {
            return [
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN is not configured in .env',
            ];
        }

        if (empty($targetChatId)) {
            return [
                'success' => false,
                'message' => 'TELEGRAM_DEFAULT_CHAT_ID is not configured in .env',
            ];
        }

        $message = $this->buildUtilizationSummaryMessage($startDate, $endDate, $title);
        $sent    = $this->send($message, $targetChatId);

        return [
            'success' => $sent,
            'message' => $sent
                ? 'Utilization summary successfully sent to Telegram.'
                : 'Failed to send summary to Telegram. Check log files for details.',
            'text'    => $message,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private internals
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Execute the HTTP call to the Telegram Bot API and log the result.
     */
    private function send(
        string       $message,
        ?string      $chatId,
        ?Transaction $transaction = null,
        string       $channel = 'telegram'
    ): bool {
        if (empty($this->botToken)) {
            Log::warning('TelegramService: TELEGRAM_BOT_TOKEN is not set. Skipping notification.');
            return false;
        }

        if (empty($chatId)) {
            Log::warning('TelegramService: No chat ID provided. Skipping notification.');
            return false;
        }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ];

        $success      = false;
        $errorMessage = null;

        try {
            $response = Http::timeout(10)
                ->retry(3, 1000)
                ->post("{$this->apiUrl}{$this->botToken}/sendMessage", $payload);

            $success = $response->successful();

            if (! $success) {
                $errorMessage = "Telegram API error {$response->status()}: " . $response->body();
                Log::error('TelegramService send failed', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'chat_id' => $chatId,
                ]);
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            Log::error('TelegramService exception', [
                'message' => $e->getMessage(),
                'chat_id' => $chatId,
            ]);
        }

        // Log every notification attempt to notification_logs for auditing
        if ($transaction) {
            NotificationLog::create([
                'transaction_id' => $transaction->id,
                'channel'        => $channel,
                'recipient'      => $chatId,
                'sent_at'        => now(),
                'success'        => $success,
                'payload'        => ['text' => $message],
                'error_message'  => $errorMessage,
            ]);
        }

        return $success;
    }
}
