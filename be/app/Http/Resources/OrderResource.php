<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'customer_no' => $this->customer_no,
            'zone_id' => $this->zone_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'selling_price' => $this->selling_price,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status?->value,
            'topup_status' => $this->topup_status?->value,
            'midtrans_snap_token' => $this->midtrans_snap_token,
            'digiflazz_sn' => $this->digiflazz_sn,
            'failure_reason' => $this->failure_reason,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'product' => new ProductResource($this->whenLoaded('product')),
            'logs' => OrderLogResource::collection($this->whenLoaded('logs')),
        ];
    }
}
