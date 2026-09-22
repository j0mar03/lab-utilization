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

    public const SOFTWARE_CATALOG = [
        'Adobe Creative Cloud' => [
            'name'        => 'Adobe Creative Cloud',
            'short'       => 'Adobe CC',
            'icon'        => '🎨',
            'category'    => 'Design & Multimedia',
            'description' => 'Photoshop, Premiere Pro, Illustrator, InDesign, After Effects',
            'departments' => ['DOMIT', 'DECET'],
        ],
        'Huawei eNSP' => [
            'name'        => 'Huawei eNSP',
            'short'       => 'Huawei eNSP',
            'icon'        => '🌐',
            'category'    => 'Networking Simulation',
            'description' => 'Enterprise Network Simulation Platform (Routers, Switches, Firewalls, WLAN)',
            'departments' => ['DECET'],
        ],
        'Microsoft Office 365' => [
            'name'        => 'Microsoft Office 365',
            'short'       => 'MS Office 365',
            'icon'        => '📄',
            'category'    => 'Office Productivity',
            'description' => 'Word, Excel, PowerPoint, Access, Teams, Outlook',
            'departments' => ['DOMIT', 'DEMET', 'DECET'],
        ],
        'Engineering Software' => [
            'name'        => 'Engineering Software',
            'short'       => 'Engineering CAD',
            'icon'        => '📐',
            'category'    => 'CAD & Simulation',
            'description' => 'AutoCAD, SolidWorks, MATLAB, Proteus, NI Multisim, FluidSIM',
            'departments' => ['DEMET', 'DECET'],
        ],
        'Programming & IDEs' => [
            'name'        => 'Programming & IDEs',
            'short'       => 'Programming / IDEs',
            'icon'        => '💻',
            'category'    => 'Software Development',
            'description' => 'VS Code, Python, Java JDK, Android Studio, Arduino IDE, Git',
            'departments' => ['DOMIT', 'DECET'],
        ],
        'Database & Server Tools' => [
            'name'        => 'Database & Server Tools',
            'short'       => 'DB & Server Tools',
            'icon'        => '🗄️',
            'category'    => 'Databases & Networking',
            'description' => 'MySQL Workbench, XAMPP, Cisco Packet Tracer, Wireshark',
            'departments' => ['DOMIT', 'DECET'],
        ],
    ];

    protected $fillable = [
        'user_id',
        'room_id',
        'tool_id',
        'quantity',
        'borrower_name',
        'borrower_email',
        'department',
        'subject',
        'software_utilized',
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
            'software_utilized'  => 'array',
        ];
    }

    public function hasSoftwareUtilized(): bool
    {
        return !empty($this->software_utilized) && count($this->software_utilized) > 0;
    }

    public function softwareSummary(): string
    {
        if (!$this->hasSoftwareUtilized()) {
            return '';
        }

        return implode(', ', (array) $this->software_utilized);
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (empty($transaction->department)) {
                $transaction->department = static::resolveDepartmentFor(
                    $transaction->room_id,
                    $transaction->tool_id,
                    $transaction->borrower_name,
                    $transaction->subject
                );
            }
        });
    }

    public static function resolveDepartmentFor(?int $roomId = null, ?int $toolId = null, ?string $borrowerName = null, ?string $subject = null): ?string
    {
        if ($roomId) {
            $room = Room::find($roomId);
            if ($room && !empty($room->department)) {
                return $room->department;
            }
        }

        if ($toolId) {
            $tool = Tool::find($toolId);
            if ($tool && !empty($tool->department)) {
                return $tool->department;
            }
        }

        if (!empty($borrowerName)) {
            $cleanBorrower = strtolower(trim(str_replace(['engr.', 'prof.', 'dr.', 'mr.', 'ms.', 'mrs.', ','], '', $borrowerName)));
            $borrowerWords = array_filter(explode(' ', $cleanBorrower), fn($w) => strlen($w) > 2);

            $faculties = Faculty::all();
            foreach ($faculties as $f) {
                $cleanFaculty = strtolower(trim(str_replace(['engr.', 'prof.', 'dr.', 'mr.', 'ms.', 'mrs.', ','], '', $f->name)));
                if (str_contains($cleanBorrower, $cleanFaculty) || str_contains($cleanFaculty, $cleanBorrower)) {
                    return $f->department;
                }

                $facultyWords = array_filter(explode(' ', $cleanFaculty), fn($w) => strlen($w) > 2);
                // Check if last name or main name word matches
                $matchedWords = array_intersect($borrowerWords, $facultyWords);
                if (count($matchedWords) >= 1 && (count($borrowerWords) <= 2 || count($matchedWords) >= 2)) {
                    return $f->department;
                }
            }
        }

        if (!empty($subject)) {
            $cleanSubj = strtolower($subject);
            $subjects = Subject::all();
            foreach ($subjects as $s) {
                if (str_contains($cleanSubj, strtolower($s->code)) || str_contains($cleanSubj, strtolower($s->name))) {
                    return $s->department;
                }
            }
        }

        return null;
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

    /**
     * Check if an active session is stale (abandoned from a previous day or overdue by > 6 hours).
     */
    public function isStale(): bool
    {
        if (! in_array($this->status, ['open', 'partially_returned', 'overdue'])) {
            return false;
        }

        if ($this->checked_out_at && $this->checked_out_at->lt(now()->startOfDay())) {
            return true;
        }

        if ($this->expected_return_at && $this->expected_return_at->lt(now()->subHours(6))) {
            return true;
        }

        return false;
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
        return $query->whereIn('status', ['open', 'partially_returned', 'overdue']);
    }

    public function scopeStale($query)
    {
        return $query->whereIn('status', ['open', 'partially_returned', 'overdue'])
            ->where(function ($q) {
                $q->where('checked_out_at', '<', now()->startOfDay())
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('expected_return_at')
                          ->where('expected_return_at', '<', now()->subHours(6));
                  });
            });
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($sub) {
                  $sub->whereIn('status', ['open', 'partially_returned'])
                      ->whereNotNull('expected_return_at')
                      ->where('expected_return_at', '<', now());
              });
        });
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeRooms($query)
    {
        return $query->whereNotNull('room_id');
    }

    public function scopeTools($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('tool_id')
              ->orWhereHas('items');
        });
    }

    public function scopeDepartment($query, ?string $dept)
    {
        if ($dept) {
            return $query->where('department', $dept);
        }
        return $query;
    }

    public function isRoom(): bool
    {
        return $this->room_id !== null;
    }

    public function isTool(): bool
    {
        return $this->tool_id !== null || ($this->relationLoaded('items') ? $this->items->isNotEmpty() : $this->items()->exists());
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
            default => $this->department ? substr($this->department, 0, 15) : 'General',
        };
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
        return $this->status === 'overdue' || $this->isPastDue();
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'partially_returned', 'overdue']) && $this->returned_at === null;
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
