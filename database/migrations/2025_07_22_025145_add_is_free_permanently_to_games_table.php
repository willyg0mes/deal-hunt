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
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_free_permanently')->default(false);
            $table->timestamp('free_since')->nullable();
            $table->text('free_description')->nullable();
            $table->index('is_free_permanently');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['is_free_permanently']);
            $table->dropColumn(['is_free_permanently', 'free_since', 'free_description']);
        });
    }
};
