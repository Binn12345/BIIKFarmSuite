<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'item_record_id',
        'movement_type',
        'quantity',
        'balance_after',
        'reference',
        'source_record_id',
        'actor_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'balance_after' => 'decimal:3',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(FarmRecord::class, 'item_record_id');
    }

    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(FarmRecord::class, 'source_record_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stock movement entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Stock movement entries are immutable.'));
    }
}
