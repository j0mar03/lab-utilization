<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TelegramService
 *
 * Sends messages to a Telegram chat via the Bot API.
 * All bot credentials are read from config/telegram.php (which reads .env).
 * No tokens are hardcoded here.
 *
 * Usage:
 *   app(TelegramService::class)->sendCheckoutNotification($transaction);
 *   app(TelegramService::class)->sendReturnNotification($transaction);
 *   app(TelegramService::class)->sendRawMessage('Hello, lab!', $chatId);
 */
class TelegramService
{
    private string $botToken;
    private string $apiUrl;
    private ?string $defaultChatId;

    public function __construct()
    {
        $this->botToken     = config('telegram.bot_token', '');
        $this->apiUrl       = config('telegram.api_url', 'https://api.telegram.org/bot');
        $this->defaultChatId = config('telegram.default_chat_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public notification methods
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Send a checkout notification to the lab Telegram group.
     * This mirrors the notification your current Apps Script sends.
     */
    public function sendCheckoutNotification(Transaction $transaction): bool
    {
        $subject = $transaction->subjectDescription();
        $time    = $transaction->checked_out_at->format('M d, Y g:i A');

        $message  = "🔑 *LAB CHECKOUT*\n";
        $message .= "───────────────────\n";
        $message .= "👤 *Faculty:* {$transaction->borrower_name}\n";
        $message .= "📦 *Room/Tool:* {$subject}\n";

        if ($transaction->subject) {
            $message .= "📚 *Subject:* {$transaction->subject}\n";
        }

        $message .= "🕐 *Time:* {$time}\n";

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
        $subject    = $transaction->subjectDescription();
        $returnTime = $transaction->returned_at->format('M d, Y g:i A');

        $message  = "✅ *ITEM RETURNED*\n";
        $message .= "───────────────────\n";
        $message .= "👤 *Returned by:* {$transaction->borrower_name}\n";
        $message .= "📦 *Item:* {$subject}\n";
        $message .= "🕐 *Returned at:* {$returnTime}\n";

        // Flag if it was overdue before return
        if ($transaction->expected_return_at &&
            $transaction->returned_at->gt($transaction->expected_return_at)) {
            $message .= "⚠️ *Note:* Returned after expected time.\n";
        }

        return $this->send($message, $this->defaultChatId, $transaction, 'telegram');
    }

    /**
     * Send an overdue alert to the lab Telegram group.
     * Typically called by a scheduled Artisan command.
     */
    public function sendOverdueAlert(Transaction $transaction): bool
    {
        $subject    = $transaction->subjectDescription();
        $since      = $transaction->expected_return_at->format('M d, Y g:i A');

        $message  = "🚨 *OVERDUE ITEM*\n";
        $message .= "───────────────────\n";
        $message .= "👤 *Borrower:* {$transaction->borrower_name}\n";
        $message .= "📦 *Item:* {$subject}\n";
        $message .= "⏰ *Was due at:* {$since}\n";
        $message .= "📌 Please follow up with the borrower.\n";

        return $this->send($message, $this->defaultChatId, $transaction, 'telegram');
    }

    /**
     * Send a raw text message to a specific chat.
     * Useful for testing or custom one-off notifications.
     *
     * @param string      $message  Plain text or Markdown message
     * @param string|null $chatId   Defaults to TELEGRAM_DEFAULT_CHAT_ID if null
     */
    public function sendRawMessage(string $message, ?string $chatId = null): bool
    {
        return $this->send($message, $chatId ?? $this->defaultChatId);
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
