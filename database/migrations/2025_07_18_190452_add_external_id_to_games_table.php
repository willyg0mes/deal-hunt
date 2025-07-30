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
            $table->string('external_id')->nullable()->unique()->after('id');
            $table->string('developer')->nullable()->after('store_name');
            $table->date('release_date')->nullable()->after('developer');
            $table->integer('metacritic')->nullable()->after('release_date');
            $table->boolean('is_active')->default(true)->after('metacritic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'developer', 'release_date', 'metacritic', 'is_active']);
        });
    }
};
