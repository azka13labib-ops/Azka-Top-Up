<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'thumbnail_url', 'description',
    'id_field_label', 'id_field_placeholder', 'zone_field_label',
    'needs_zone', 'is_active', 'sort_order'
])]
class Game extends Model
{
    protected function casts(): array
    {
        return [
            'needs_zone' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
