<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use SoftDeletes;

    /**
     * Statuses available for inventory assets.
     *
     * Keep report filters aligned with the statuses that can be assigned to an
     * item in Inventory.
     */
    public const STATUS_OPTIONS = [
        'available' => 'Available',
        'ongoodcondition' => 'On Good Condition',
        'borrowed' => 'Borrowed',
        'damaged' => 'Damaged / Broken Parts',
        'maintenance' => 'Maintenance',
        'disposed' => 'Disposed',
    ];

    protected $fillable = [
        'property_tag',
        'name',
        'category_id',
        'tag_id',
        'location_id',
        'supplier_id',
        'status',
        'serial_number',
        'acquisition_cost',
        'acquisition_date',
        'accountable_personnel',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(AssetTag::class, 'tag_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * The history of this item being borrowed/returned.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
