<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use HasFactory, SoftDeletes;

    public const DEPARTMENTS = [
        'Department of Office Management and Information Technology',
        'Department of Computer and Electronics Engineering Technology',
        'Department of Electrical and Mechanical Engineering Technology',
    ];

    protected $fillable = [
        'name',
        'category',
        'department',
        'description',
        'total_quantity',
        'room_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity' => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    public function departmentShort(): string
    {
        return match($this->department) {
            'Department of Office Management and Information Technology' => 'DOMIT',
            'Department of Computer and Electronics Engineering Technology' => 'DECET',
            'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
            default => $this->department ? substr($this->department, 0, 15) : 'General',
        };
    }

    public function scopeDepartment($query, ?string $department)
    {
        if ($department) {
            return $query->where('department', $department);
        }
        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * The room this tool is homed to (e.g. a room-specific key).
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * All checkout transactions for this tool.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * All line items referencing this tool.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Computed availability
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * How many units are currently borrowed (open or partially returned).
     */
    public function getBorrowedQuantityAttribute(): int
    {
        $itemsBorrowed = (int) TransactionItem::where('tool_id', $this->id)
            ->where('status', '!=', 'returned')
            ->whereHas('transaction', function ($q) {
                $q->whereIn('status', ['open', 'partially_returned', 'overdue']);
            })
            ->sum(\Illuminate\Support\Facades\DB::raw('quantity_borrowed - quantity_returned'));

        $legacyBorrowed = (int) $this->transactions()
            ->whereIn('status', ['open', 'overdue'])
            ->whereDoesntHave('items')
            ->sum('quantity');

        return $itemsBorrowed + $legacyBorrowed;
    }

    /**
     * How many units are currently overdue.
     */
    public function getOverdueQuantityAttribute(): int
    {
        $itemsOverdue = (int) TransactionItem::where('tool_id', $this->id)
            ->where('status', '!=', 'returned')
            ->whereHas('transaction', function ($q) {
                $q->overdue();
            })
            ->sum(\Illuminate\Support\Facades\DB::raw('quantity_borrowed - quantity_returned'));

        $legacyOverdue = (int) $this->transactions()
            ->overdue()
            ->whereDoesntHave('items')
            ->sum('quantity');

        return $itemsOverdue + $legacyOverdue;
    }

    /**
     * Get all active checkouts across direct borrows and room borrows.
     */
    public function getActiveCheckoutsAttribute()
    {
        return TransactionItem::where('tool_id', $this->id)
            ->where('status', '!=', 'returned')
            ->whereHas('transaction', fn($q) => $q->whereIn('status', ['open', 'partially_returned', 'overdue']))
            ->with(['transaction.room', 'transaction.user'])
            ->get();
    }

    /**
     * How many units are available right now.
     * available = total_quantity - currently borrowed
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->total_quantity - $this->borrowed_quantity);
    }

    /**
     * Whether at least 1 unit can be borrowed right now.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->available_quantity > 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
