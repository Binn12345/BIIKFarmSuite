<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SalePayment extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['sale_id', 'amount', 'method', 'reference', 'recorded_by'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(FarmRecord::class, 'sale_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Sale payment entries are immutable.'));
        static::deleting(fn () => throw new LogicException('Sale payment entries are immutable.'));
    }
}
