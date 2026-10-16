<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module',
        'reference',
        'title',
        'record_date',
        'status',
        'quantity',
        'weight',
        'unit_price',
        'amount',
        'unit',
        'category',
        'related_animal_id',
        'related_record_id',
        'data',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'quantity' => 'decimal:3',
            'weight' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'data' => 'array',
        ];
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'related_animal_id');
    }

    public function relatedRecord(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_record_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'item_record_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class, 'sale_id');
    }
}
