<?php

namespace App\Repositories;

use App\Contracts\Interfaces\data\OrderInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderInterface
{
    public function find(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByCode(string $orderCode): ?Order
    {
        return Order::where('order_code', $orderCode)->first();
    }

    public function findByMidtransOrderId(string $midtransOrderId): ?Order
    {
        return Order::where('midtrans_order_id', $midtransOrderId)->first();
    }

    public function createOrder(array $data): Order
    {
        return Order::create($data);
    }

    public function updateStatus(int $orderId, array $statusData): bool
    {
        return Order::where('id', $orderId)->update($statusData) > 0;
    }

    public function getLatestOrders(int $limit = 10): Collection
    {
        return Order::with('product.game')->latest()->limit($limit)->get();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return Order::with('product.game')->latest()->paginate($perPage, $columns, $pageName, $page);
    }

    public function paginateByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['product.game'])->where('user_id', $userId)->latest()->paginate($perPage);
    }

    public function paginateFiltered(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['product.game'])->latest();

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['topup_status'])) {
            $query->where('topup_status', $filters['topup_status']);
        }

        if (isset($filters['game_id'])) {
            $query->whereHas('product', function ($q) use ($filters) {
                $q->where('game_id', $filters['game_id']);
            });
        }

        return $query->paginate($perPage);
    }

    public function search(string $query, array $columns)
    {
        return Order::where(function ($q) use ($query, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$query}%");
            }
        });
    }
}
