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
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 3)->default('BRL')->after('email'); // Moeda preferida (BRL, USD, EUR, etc.)
            $table->string('locale', 5)->default('pt-BR')->after('currency'); // Idioma/localização (pt-BR, en-US, etc.)
            $table->decimal('currency_rate', 8, 4)->default(1.0000)->after('locale'); // Taxa de conversão (atualizada automaticamente)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'locale', 'currency_rate']);
        });
    }
};
