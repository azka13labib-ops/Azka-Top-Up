<?php

namespace App\Repositories;

use App\Contracts\Interfaces\data\OrderLogInterface;
use App\Models\OrderLog;
use Illuminate\Database\Eloquent\Collection;

class OrderLogRepository implements OrderLogInterface
{
    public function createLog(int $orderId, string $event, ?array $payload = null): OrderLog
    {
        return OrderLog::create([
            'order_id' => $orderId,
            'event' => $event,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    public function getLogsByOrderId(int $orderId): Collection
    {
        return OrderLog::where('order_id', $orderId)->orderBy('created_at', 'asc')->get();
    }
}
