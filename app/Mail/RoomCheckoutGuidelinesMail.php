<?php

namespace App\Mail;

use App\Models\NotificationLog;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RoomCheckoutGuidelinesMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->loadMissing(['room', 'tool', 'items.tool']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $roomName = $this->transaction->room?->name ?? 'Room';
        $appName  = config('app.name', 'PUP-ITECH Lab System');

        return new Envelope(
            subject: "[{$appName}] Facility Usage Guidelines & Confirmation: {$roomName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $room = $this->transaction->room;

        $roomType = 'general';
        if ($room) {
            if ($room->isComputerLab()) {
                $roomType = 'computer_lab';
            } elseif ($room->isEngineeringLab()) {
                $roomType = 'engineering_lab';
            } elseif ($room->isLecture()) {
                $roomType = 'lecture';
            } elseif ($room->isOffice()) {
                $roomType = 'office';
            }
        }

        return new Content(
            view: 'emails.room-guidelines',
            with: [
                'transaction' => $this->transaction,
                'room'        => $room,
                'roomType'    => $roomType,
                'manualUrl'   => $room?->manual_url,
            ],
        );
    }

    /**
     * Log successful email sending to notification_logs table.
     */
    public function callbacks(): array
    {
        return [
            function ($message) {
                if (!$this->transaction->exists) {
                    return;
                }
                try {
                    NotificationLog::create([
                        'transaction_id' => $this->transaction->id,
                        'channel'        => 'email',
                        'recipient'      => $this->transaction->borrower_email,
                        'sent_at'        => now(),
                        'success'        => true,
                        'payload'        => [
                            'subject'  => "[PUP-ITECH Lab] Facility Guidelines: " . ($this->transaction->room?->name ?? ''),
                            'room'     => $this->transaction->room?->name,
                            'borrower' => $this->transaction->borrower_name,
                        ],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to log email notification to notification_logs: ' . $e->getMessage());
                }
            }
        ];
    }
}
