<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\TopupStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_code', 'user_id', 'product_id', 'customer_no', 'zone_id',
    'email', 'phone', 'selling_price', 'payment_method', 'payment_status',
    'topup_status', 'midtrans_order_id', 'midtrans_snap_token',
    'digiflazz_ref_id', 'digiflazz_sn', 'failure_reason',
    'paid_at', 'completed_at', 'refund_flagged_at', 'refund_notes'
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'payment_status' => PaymentStatusEnum::class,
            'topup_status' => TopupStatusEnum::class,
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'refund_flagged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }
}
