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
        Schema::table('usuarios', function (Blueprint $table) {
            $table->date('data_inicio_contrato')->nullable()->after('nivel');
            $table->date('data_fim_contrato')->nullable()->after('data_inicio_contrato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('data_inicio_contrato');
            $table->dropColumn('data_fim_contrato');
        });
    }
};
