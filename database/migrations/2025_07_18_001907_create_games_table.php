<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('normal_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->integer('discount_percent');
            $table->string('store')->nullable();
            $table->string('store_name')->nullable();
            $table->text('deal_url');
            $table->text('resolved_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('deal_ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
