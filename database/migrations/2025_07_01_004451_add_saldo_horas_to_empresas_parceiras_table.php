<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EmpresaParceira;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas_parceiras', function (Blueprint $table) {
            $table->decimal('saldo_horas', 10, 2)->default(0.00)->after('horas_contratadas');
        });

        // Atualiza o saldo inicial para todas as empresas existentes
        EmpresaParceira::all()->each(function ($empresa) {
            $empresa->update(['saldo_horas' => $empresa->horas_contratadas]);
        });
    }

    public function down(): void
    {
        Schema::table('empresas_parceiras', function (Blueprint $table) {
            $table->dropColumn('saldo_horas');
        });
    }
};