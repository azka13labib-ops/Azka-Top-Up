<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'thumbnail_url' => $this->thumbnail_url,
            'description' => $this->description,
            'id_field_label' => $this->id_field_label,
            'id_field_placeholder' => $this->id_field_placeholder,
            'zone_field_label' => $this->zone_field_label,
            'needs_zone' => $this->needs_zone,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
