<?php

namespace App\Models;

use App\Enums\MarkupTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'game_id', 'digiflazz_sku', 'name', 'description',
    'base_price', 'selling_price', 'markup_type', 'markup_value', 'is_active'
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'markup_type' => MarkupTypeEnum::class,
            'base_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'markup_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
