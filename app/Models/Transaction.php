<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Transaction model.
 *
 * IMMUTABILITY CONTRACT:
 * Records are append-only after creation — corrections are new records.
 * Only returned_at, status, and (for tools) quantity adjustments via return
 * are modified after creation. This preserves a clean audit trail suitable
 * for reporting and utilization research.
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'tool_id',
        'quantity',
        'borrower_name',
        'borrower_email',
        'subject',
        'checked_out_at',
        'expected_return_at',
        'returned_at',
        'status',
        'notes',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at'     => 'datetime',
            'expected_return_at' => 'datetime',
            'returned_at'        => 'datetime',
            'quantity'           => 'integer',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /**
     * Line items for multi-tool checkout.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePartiallyReturned($query)
    {
        return $query->where('status', 'partially_returned');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'partially_returned']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isPartiallyReturned(): bool
    {
        return $this->status === 'partially_returned';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function getRemainingQuantityAttribute(): int
    {
        if ($this->relationLoaded('items') || $this->items()->exists()) {
            return (int) $this->items->sum(fn($i) => $i->remaining_quantity);
        }

        return $this->status === 'returned' ? 0 : $this->quantity;
    }

    public function getTotalQuantityBorrowedAttribute(): int
    {
        if ($this->relationLoaded('items') || $this->items()->exists()) {
            return (int) $this->items->sum('quantity_borrowed');
        }

        return $this->quantity;
    }

    public function getTotalQuantityReturnedAttribute(): int
    {
        if ($this->relationLoaded('items') || $this->items()->exists()) {
            return (int) $this->items->sum('quantity_returned');
        }

        return $this->status === 'returned' ? $this->quantity : 0;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    /**
     * Whether this transaction is past its expected return time.
     */
    public function isPastDue(): bool
    {
        return $this->expected_return_at !== null
            && $this->returned_at === null
            && $this->expected_return_at->isPast();
    }

    /**
     * Human-readable description of what was borrowed.
     * Used in Telegram messages.
     */
    public function subjectDescription(): string
    {
        $parts = [];

        if ($this->room) {
            $parts[] = "Room: {$this->room->name}";
        }

        if ($this->relationLoaded('items') ? $this->items->isNotEmpty() : $this->items()->exists()) {
            $toolItems = $this->items->map(function ($item) {
                return ($item->tool?->name ?? 'Tool') . " (×{$item->quantity_borrowed})";
            })->join(', ');
            $parts[] = "Tools: {$toolItems}";
        } elseif ($this->tool) {
            $qty     = $this->quantity > 1 ? " (×{$this->quantity})" : '';
            $parts[] = "Tool: {$this->tool->name}{$qty}";
        }

        return implode(' + ', $parts) ?: 'Unknown item';
    }
}
