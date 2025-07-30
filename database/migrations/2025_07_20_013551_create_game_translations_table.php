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
        Schema::create('game_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('field_name'); // 'description', 'title', etc.
            $table->text('original_text'); // Texto original em inglês
            $table->text('translated_text'); // Texto traduzido em português
            $table->string('translation_method')->default('automatic'); // 'automatic', 'manual', 'api'
            $table->float('confidence_score')->nullable(); // Score de confiança da tradução (0-1)
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['game_id', 'field_name']);
            $table->unique(['game_id', 'field_name'], 'game_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_translations');
    }
};
