<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'game_id' => $this->game_id,
            'digiflazz_sku' => $this->digiflazz_sku,
            'name' => $this->name,
            'description' => $this->description,
            'selling_price' => $this->selling_price,
            'is_active' => $this->is_active,
            'game' => new GameResource($this->whenLoaded('game')),
        ];
    }
}
