<?php

namespace App\Contracts\Interfaces\data;

use App\Models\OrderLog;
use Illuminate\Database\Eloquent\Collection;

interface OrderLogInterface
{
    public function createLog(int $orderId, string $event, ?array $payload = null): OrderLog;
    public function getLogsByOrderId(int $orderId): Collection;
}
