<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    protected $signature   = 'telegram:test {--chat-id= : Optional custom Telegram chat ID}';
    protected $description = 'Send a test verification message to Telegram via the configured bot';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Testing Telegram Bot configuration...');

        $botToken = config('telegram.bot_token');
        $defaultChatId = config('telegram.default_chat_id');
        $chatId = $this->option('chat-id') ?: $defaultChatId;

        $this->line('Bot Token:  ' . ($botToken ? substr($botToken, 0, 8) . '...' . substr($botToken, -4) : '<not configured>'));
        $this->line('Target Chat ID: ' . ($chatId ?: '<not configured>'));

        if (empty($botToken)) {
            $this->error('❌ TELEGRAM_BOT_TOKEN is not configured in your .env file.');
            $this->comment('Set TELEGRAM_BOT_TOKEN in .env with the token provided by @BotFather.');
            return self::FAILURE;
        }

        if (empty($chatId)) {
            $this->error('❌ TELEGRAM_DEFAULT_CHAT_ID is not configured in your .env file.');
            $this->comment('Set TELEGRAM_DEFAULT_CHAT_ID in .env with your Telegram group or personal chat ID.');
            return self::FAILURE;
        }

        $result = $telegram->testConnection($chatId);

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            return self::SUCCESS;
        } else {
            $this->error('❌ ' . $result['message']);
            return self::FAILURE;
        }
    }
}
