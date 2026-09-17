<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * MarkOverdueTransactions
 *
 * Scheduled Artisan command that:
 *  1. Finds all open transactions past their expected return time
 *  2. Marks their status as 'overdue'
 *  3. Sends a Telegram alert to the lab group for each newly-overdue item
 *
 * Schedule: every 15 minutes (see routes/console.php)
 *
 * Run manually:
 *   php artisan lab:check-overdue
 *
 * Dry-run (see what would be marked without doing anything):
 *   php artisan lab:check-overdue --dry-run
 */
class MarkOverdueTransactions extends Command
{
    protected $signature   = 'lab:check-overdue {--dry-run : Preview without making changes}';
    protected $description = 'Mark overdue transactions and send Telegram alerts';

    public function __construct(private TelegramService $telegram)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN — no changes will be made.');
        }

        // Find open transactions that are past their expected return time
        // and haven't been marked overdue yet
        $overdueTransactions = Transaction::where('status', 'open')
            ->whereNotNull('expected_return_at')
            ->where('expected_return_at', '<', now())
            ->with(['room', 'tool'])
            ->get();

        if ($overdueTransactions->isEmpty()) {
            $this->info('No overdue transactions found.');
            return self::SUCCESS;
        }

        $this->info("Found {$overdueTransactions->count()} overdue transaction(s).");

        foreach ($overdueTransactions as $transaction) {
            $label = $transaction->subjectDescription();
            $since = $transaction->expected_return_at->diffForHumans();

            $this->warn("  Overdue: #{$transaction->id} — {$transaction->borrower_name} | {$label} | due {$since}");

            if (! $dryRun) {
                // Mark as overdue
                $transaction->update(['status' => 'overdue']);

                // Send Telegram alert
                $this->telegram->sendOverdueAlert($transaction);

                Log::info('Transaction marked overdue', [
                    'transaction_id' => $transaction->id,
                    'borrower'       => $transaction->borrower_name,
                    'item'           => $label,
                    'was_due_at'     => $transaction->expected_return_at,
                ]);
            }
        }

        if (! $dryRun) {
            $this->info("✅ {$overdueTransactions->count()} transaction(s) marked overdue. Telegram alerts sent.");
        }

        return self::SUCCESS;
    }
}
