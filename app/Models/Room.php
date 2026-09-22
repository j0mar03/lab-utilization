<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    public const DEPARTMENTS = Tool::DEPARTMENTS;

    protected $fillable = [
        'name',
        'department',
        'location',
        'capacity',
        'has_wifi',
        'wifi_notes',
        'manual_url',
    ];

    protected function casts(): array
    {
        return [
            'has_wifi' => 'boolean',
            'capacity' => 'integer',
        ];
    }

    public function departmentShort(): string
    {
        return match($this->department) {
            'Department of Office Management and Information Technology' => 'DOMIT',
            'Department of Computer and Electronics Engineering Technology' => 'DECET',
            'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
            'DEMET & DOMIT', 'DEMET / DOMIT', 'DEMET and DOMIT' => 'DEMET & DOMIT',
            'Department of Civil and Railway Engineering Technology' => 'DCRET',
            'College of Science' => 'CS',
            default => $this->department ? substr($this->department, 0, 15) : 'General / Shared',
        };
    }

    public const COMPUTER_LABS = [
        'LAB 104',
        'LAB 105',
        'LAB 109C',
        'LAB 203',
        'LAB 204',
        'LAB 205',
    ];

    public const ENGINEERING_LABS = [
        'LAB 109',
        'LAB 109B',
        'LAB 208',
    ];

    public function isComputerLab(): bool
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $this->name)));
        return in_array($normalized, self::COMPUTER_LABS);
    }

    public function isEngineeringLab(): bool
    {
        $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $this->name)));
        return in_array($normalized, self::ENGINEERING_LABS);
    }

    public function isOffice(): bool
    {
        return str_contains(strtoupper($this->name), 'OFFICE');
    }

    public function isLab(): bool
    {
        return str_starts_with(strtoupper($this->name), 'LAB') && !$this->isOffice();
    }

    public function isLecture(): bool
    {
        return str_starts_with(strtoupper($this->name), 'LEC');
    }

    public function roomType(): string
    {
        if ($this->isComputerLab()) return 'Computer Lab';
        if ($this->isEngineeringLab()) return 'Engineering Lab';
        if ($this->isLab()) return 'Laboratory';
        if ($this->isOffice()) return 'Office';
        if ($this->isLecture()) return 'Lecture Room';
        return 'General Room';
    }

    public function roomTypeBadge(): string
    {
        if ($this->isComputerLab()) return '💻 Computer Lab';
        if ($this->isEngineeringLab()) return '⚙️ Engineering Lab';
        if ($this->isLab()) return '🔬 Laboratory';
        if ($this->isOffice()) return '🏢 Office';
        if ($this->isLecture()) return '📖 Lecture';
        return '🏫 General';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tools that are homed to this room (e.g. room keys).
     */
    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    /**
     * All checkout transactions for this room.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the active / current open transaction for this room if any.
     */
    public function currentTransaction(): ?Transaction
    {
        if ($this->relationLoaded('transactions')) {
            return $this->transactions->first(fn ($t) => in_array($t->status, ['open', 'partially_returned', 'overdue']));
        }

        return $this->transactions()
            ->whereIn('status', ['open', 'partially_returned', 'overdue'])
            ->latest('checked_out_at')
            ->first();
    }

    /**
     * Check if this room is currently in use / occupied.
     */
    public function isOccupied(): bool
    {
        return $this->currentTransaction() !== null;
    }

    /**
     * Check if the room's current active transaction is stale (abandoned from previous day / long overdue).
     */
    public function hasStaleTransaction(): bool
    {
        return $this->currentTransaction()?->isStale() ?? false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Rooms that currently have an open or overdue checkout (still occupied).
     */
    public function scopeOccupied($query)
    {
        return $query->whereHas('transactions', function ($q) {
            $q->whereIn('status', ['open', 'partially_returned', 'overdue']);
        });
    }

    /**
     * Rooms with no active checkout — truly available right now.
     */
    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('transactions', function ($q) {
            $q->whereIn('status', ['open', 'partially_returned', 'overdue']);
        });
    }

    public function scopeDepartment($query, ?string $dept)
    {
        if ($dept) {
            return $query->where('department', $dept);
        }
        return $query;
    }

    public function scopeOffices($query)
    {
        return $query->where('name', 'LIKE', '%Office%');
    }

    public function scopeLaboratories($query)
    {
        return $query->where('name', 'LIKE', 'LAB%')->where('name', 'NOT LIKE', '%Office%');
    }

    public function scopeComputerLabs($query)
    {
        return $query->whereIn('name', self::COMPUTER_LABS);
    }

    public function scopeEngineeringLabs($query)
    {
        return $query->whereIn('name', self::ENGINEERING_LABS);
    }

    public function scopeLectures($query)
    {
        return $query->where('name', 'LIKE', 'LEC%');
    }
}
