<?php

namespace App\Services;

use App\Models\NotificationLog;
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
        $icon       = $isRoom ? '🏫' : '✅';
        $title      = $isRoom ? 'ROOM VACATED' : ($transaction->status === 'partially_returned' ? 'PARTIAL RETURN' : 'ITEM RETURNED');
        $dept       = $transaction->departmentShort() ?: 'General / Shared';
        $returnTime = $transaction->returned_at ? $transaction->returned_at->format('M d, Y g:i A') : now()->format('M d, Y g:i A');

        $message  = "{$icon} *{$title}*\n";
        $message .= "─────────────────────────\n";
        $message .= "👤 *Borrower:* {$transaction->borrower_name}\n";
        $message .= "🏢 *Department:* {$dept}\n";

        if ($isRoom) {
            $message .= "🚪 *Room:* " . ($transaction->room?->name ?? 'Room') . "\n";
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
