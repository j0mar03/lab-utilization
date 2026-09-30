<?php

namespace App\Console\Commands;

use App\Mail\RoomCheckoutGuidelinesMail;
use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestRoomGuidelinesEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lab:test-guidelines-email
                            {email : Target recipient email address}
                            {--room=LAB 104 : Room name to test (e.g. LAB 104, LAB 208, LEC 201)}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test room checkout and guidelines email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $roomName = $this->option('room');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: {$email}");
            return self::FAILURE;
        }

        $this->info("Looking for room '{$roomName}'...");
        $room = Room::where('name', $roomName)->first();

        if (!$room) {
            $room = Room::first();
            $this->comment("Room '{$roomName}' not found. Using fallback room: '{$room->name}'.");
        } else {
            $this->line("Using room: {$room->name} ({$room->roomType()})");
        }

        // Look for an existing transaction or create an in-memory sample
        $transaction = Transaction::where('room_id', $room->id)->latest('id')->first();

        if (!$transaction) {
            $transaction = new Transaction([
                'room_id'            => $room->id,
                'borrower_name'      => 'Sample Faculty Member',
                'borrower_email'     => $email,
                'subject'            => 'TEST 101 — Laboratory Procedures',
                'software_utilized'  => $room->isComputerLab() ? ['Adobe Creative Cloud', 'Programming & IDEs'] : null,
                'checked_out_at'     => now(),
                'expected_return_at' => now()->addHours(3),
                'status'             => 'open',
                'notes'              => 'Test email verification session',
            ]);
            $transaction->setRelation('room', $room);
        } else {
            // Override email for testing
            $transaction->borrower_email = $email;
        }

        $this->info("Sending test guidelines email to {$email} via " . config('mail.default', 'smtp') . "...");

        try {
            Mail::to($email)->send(new RoomCheckoutGuidelinesMail($transaction));
            $this->info("✅ Email successfully sent to {$email}!");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            $this->comment("\nTip: Check your .env MAIL_* settings (host, port, username, password, encryption).");
            return self::FAILURE;
        }
    }
}
