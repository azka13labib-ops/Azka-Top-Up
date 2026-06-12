<?php

namespace App\Contracts\Interfaces\data;

use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderInterface extends PaginateInterface, SearchInterface
{
    public function find(int $id): ?Order;
    public function findByCode(string $orderCode): ?Order;
    public function findByMidtransOrderId(string $midtransOrderId): ?Order;
    public function createOrder(array $data): Order;
    public function updateStatus(int $orderId, array $statusData): bool;
    public function getLatestOrders(int $limit = 10): Collection;
    public function paginateByUserId(int $userId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function paginateFiltered(array $filters, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
