<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('customer_no', 100);
            $table->string('zone_id', 50)->nullable();
            $table->string('email', 255);
            $table->string('phone', 20)->nullable();
            $table->decimal('selling_price', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed', 'cancelled'])->default('pending');
            $table->enum('topup_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('midtrans_order_id', 100)->nullable();
            $table->string('midtrans_snap_token', 500)->nullable();
            $table->string('digiflazz_ref_id', 100)->nullable();
            $table->string('digiflazz_sn', 500)->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refund_flagged_at')->nullable();
            $table->text('refund_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['payment_status', 'topup_status', 'created_at']);
            $table->index('digiflazz_ref_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
