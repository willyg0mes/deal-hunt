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
        Schema::create('app_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('metric_type'); // 'page_views', 'game_searches', 'api_calls', etc
            $table->string('metric_key')->nullable(); // página específica, ação específica
            $table->integer('value')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['date', 'metric_type', 'metric_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_metrics');
    }
};
