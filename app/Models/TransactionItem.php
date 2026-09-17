<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'tool_id',
        'quantity_borrowed',
        'quantity_returned',
        'status',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_borrowed' => 'integer',
            'quantity_returned' => 'integer',
            'returned_at'       => 'datetime',
        ];
    }

    /**
     * Parent transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Associated tool.
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /**
     * Unreturned / remaining quantity.
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity_borrowed - $this->quantity_returned);
    }

    /**
     * Check if this item is completely returned.
     */
    public function getIsFullyReturnedAttribute(): bool
    {
        return $this->quantity_returned >= $this->quantity_borrowed;
    }
}
