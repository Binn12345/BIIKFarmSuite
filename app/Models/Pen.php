<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pen extends Model
{
    protected $fillable = [
        'name',
        'capacity',
        'location',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }
}
