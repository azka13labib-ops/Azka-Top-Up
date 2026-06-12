<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('digiflazz_sku', 100)->unique();
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->decimal('base_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->enum('markup_type', ['flat', 'percent'])->default('flat');
            $table->decimal('markup_value', 10, 2)->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['game_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
