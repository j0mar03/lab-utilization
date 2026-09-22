<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * CloseStaleRoomSessions
 *
 * Automatically closes unreturned/stale room checkouts from previous calendar days
 * or checkouts that are severely overdue, so rooms do not indefinitely show as "IN USE".
 *
 * Run manually:
 *   php artisan lab:close-stale-sessions
 *   php artisan lab:close-stale-sessions --hours=6
 *   php artisan lab:close-stale-sessions --dry-run
 */
class CloseStaleRoomSessions extends Command
{
    protected $signature = 'lab:close-stale-sessions {--hours=6 : Hours past due to consider stale} {--dry-run : Preview without making changes}';
    protected $description = 'Automatically close abandoned/unclosed room sessions from previous days';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $hoursThreshold = (int) $this->option('hours');

        if ($dryRun) {
            $this->info('DRY RUN — no changes will be applied.');
        }

        $staleTransactions = Transaction::whereIn('status', ['open', 'partially_returned', 'overdue'])
            ->whereNotNull('room_id')
            ->where(function ($query) use ($hoursThreshold) {
                $query->where('checked_out_at', '<', now()->startOfDay())
                      ->orWhere(function ($q) use ($hoursThreshold) {
                          $q->whereNotNull('expected_return_at')
                            ->where('expected_return_at', '<', now()->subHours($hoursThreshold));
                      });
            })
            ->with(['room', 'items'])
            ->get();

        if ($staleTransactions->isEmpty()) {
            $this->info('No stale room sessions found.');
            return self::SUCCESS;
        }

        $this->info("Found {$staleTransactions->count()} stale room session(s).");

        $closed = 0;
        foreach ($staleTransactions as $tx) {
            $roomName = $tx->room?->name ?? "Room #{$tx->room_id}";
            $borrower = $tx->borrower_name;
            $checkedOut = $tx->checked_out_at?->format('Y-m-d H:i') ?? 'N/A';

            $this->warn("  Stale Session: #{$tx->id} | {$roomName} | Borrower: {$borrower} | Checked Out: {$checkedOut}");

            if (! $dryRun) {
                $returnTime = $tx->expected_return_at ?? $tx->checked_out_at->addHours(3);
                if ($returnTime->isFuture() || $returnTime->gt(now())) {
                    $returnTime = now();
                }

                $tx->update([
                    'status'      => 'returned',
                    'returned_at' => $returnTime,
                    'notes'       => trim(($tx->notes ? $tx->notes . "\n" : '') . '[Auto-closed: Unclosed session from past date marked returned]'),
                ]);

                foreach ($tx->items as $item) {
                    if ($item->status !== 'returned') {
                        $item->update([
                            'quantity_returned' => $item->quantity_borrowed,
                            'status'            => 'returned',
                            'returned_at'       => $returnTime,
                        ]);
                    }
                }

                Log::info('Stale room session auto-closed', [
                    'transaction_id' => $tx->id,
                    'room'           => $roomName,
                    'borrower'       => $borrower,
                    'checked_out_at' => $tx->checked_out_at,
                    'returned_at'    => $returnTime,
                ]);

                $closed++;
            }
        }

        if (! $dryRun) {
            $this->info("✅ Successfully closed {$closed} stale room session(s). Rooms are now vacant.");
        }

        return self::SUCCESS;
    }
}
