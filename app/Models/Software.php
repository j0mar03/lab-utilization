<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Software extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'softwares';

    protected $fillable = [
        'name',
        'short',
        'icon',
        'category',
        'description',
        'departments',
        'is_active',
        'sort_order',
    ];

    public const CATEGORIES = [
        'Design & Multimedia'       => '🎨 Design & Multimedia',
        'Networking Simulation'     => '🌐 Networking Simulation',
        'Office Productivity'       => '📄 Office Productivity',
        'CAD & Simulation'          => '📐 CAD & Simulation',
        'Software Development'      => '💻 Software Development & IDEs',
        'Databases & Server'        => '🗄️ Databases & Server Tools',
        'Cybersecurity & Forensics' => '🔒 Cybersecurity & Forensics',
        'Mathematics & Analytics'   => '📊 Mathematics & Data Analytics',
        'General Utilities'         => '⚙️ General Utilities & Other',
    ];

    public const DEPARTMENTS = [
        'DOMIT' => 'Department of Office Management and Information Technology',
        'DECET' => 'Department of Computer and Electronics Engineering Technology',
        'DEMET' => 'Department of Electrical and Mechanical Engineering Technology',
        'DCRET' => 'Department of Civil and Railway Engineering Technology',
        'CS'    => 'College of Science',
    ];

    protected function casts(): array
    {
        return [
            'departments' => 'array',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Catalog Array Formatter (Used in Checkout & Reports)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return catalog array keyed by software name for transaction forms and reports.
     */
    public static function asCatalogArray(bool $onlyActive = true): array
    {
        try {
            $query = static::query()->orderBy('sort_order', 'asc')->orderBy('name', 'asc');

            if ($onlyActive) {
                $query->where('is_active', true);
            }

            $items = $query->get();

            if ($items->isEmpty()) {
                return Transaction::SOFTWARE_CATALOG;
            }

            $catalog = [];
            foreach ($items as $item) {
                $catalog[$item->name] = [
                    'id'          => $item->id,
                    'name'        => $item->name,
                    'short'       => $item->short ?: $item->name,
                    'icon'        => $item->icon ?: '💻',
                    'category'    => $item->category ?: 'General Utilities',
                    'description' => $item->description ?: '',
                    'departments' => (array) ($item->departments ?? []),
                    'is_active'   => (bool) $item->is_active,
                ];
            }

            return $catalog;
        } catch (\Throwable $e) {
            return Transaction::SOFTWARE_CATALOG;
        }
    }
}
