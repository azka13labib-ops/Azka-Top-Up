<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('thumbnail_url', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('id_field_label', 50)->default('User ID');
            $table->string('id_field_placeholder', 100)->nullable();
            $table->string('zone_field_label', 50)->default('Zone/Server ID');
            $table->boolean('needs_zone')->default(false);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
