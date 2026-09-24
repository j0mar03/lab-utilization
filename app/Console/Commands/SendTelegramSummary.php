<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTelegramSummary extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lab:send-telegram-summary
                            {--date=today : Date to summarize: "today", "yesterday", or "YYYY-MM-DD"}
                            {--days= : Summarize the past N days instead of a single date}
                            {--chat-id= : Optional custom Telegram chat ID}
                            {--preview : Only print the generated summary to console without sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send a comprehensive utilization summary of rooms, tools, and software to the lab Telegram group';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram): int
    {
        $this->info('Generating Lab Utilization Summary...');

        $dateOpt = strtolower(trim((string) $this->option('date')));
        $daysOpt = $this->option('days');
        $chatId  = $this->option('chat-id');
        $preview = $this->option('preview');

        if ($daysOpt && is_numeric($daysOpt) && (int) $daysOpt > 0) {
            $days = (int) $daysOpt;
            $startDate = now()->subDays($days - 1)->startOfDay();
            $endDate   = now()->endOfDay();
            $title     = "PAST {$days}-DAY UTILIZATION SUMMARY";
        } elseif ($dateOpt === 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate   = now()->subDay()->endOfDay();
            $title     = "YESTERDAY'S LAB UTILIZATION SUMMARY";
        } elseif ($dateOpt === 'today' || empty($dateOpt)) {
            $startDate = now()->startOfDay();
            $endDate   = now()->endOfDay();
            $title     = 'DAILY LAB UTILIZATION SUMMARY';
        } else {
            try {
                $target = Carbon::parse($dateOpt);
                $startDate = $target->copy()->startOfDay();
                $endDate   = $target->copy()->endOfDay();
                $title     = 'LAB UTILIZATION SUMMARY';
            } catch (\Throwable $e) {
                $this->error("Invalid date format: {$dateOpt}. Please use 'today', 'yesterday', or 'YYYY-MM-DD'.");
                return self::FAILURE;
            }
        }

        $message = $telegram->buildUtilizationSummaryMessage($startDate, $endDate, $title);

        if ($preview) {
            $this->line('');
            $this->comment('--- Preview Message ---');
            $this->line($message);
            $this->comment('-----------------------');
            return self::SUCCESS;
        }

        $result = $telegram->sendUtilizationSummary($startDate, $endDate, $chatId, $title);

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            $this->line('Summary preview:');
            $this->line($result['text']);
            return self::SUCCESS;
        } else {
            $this->error('❌ ' . $result['message']);
            $this->comment('Tip: Check TELEGRAM_BOT_TOKEN and TELEGRAM_DEFAULT_CHAT_ID in your .env file.');
            return self::FAILURE;
        }
    }
}
