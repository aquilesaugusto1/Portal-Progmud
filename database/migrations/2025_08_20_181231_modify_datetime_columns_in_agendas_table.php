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
        Schema::table('agendas', function (Blueprint $table) {
            // Adiciona as novas colunas se elas não existirem
            if (!Schema::hasColumn('agendas', 'data')) {
                $table->date('data')->after('assunto')->nullable();
            }
            if (!Schema::hasColumn('agendas', 'hora_inicio')) {
                $table->time('hora_inicio')->after('data')->nullable();
            }
            if (!Schema::hasColumn('agendas', 'hora_fim')) {
                $table->time('hora_fim')->after('hora_inicio')->nullable();
            }
        });

        // Popula as novas colunas com base na antiga
        DB::table('agendas')->whereNotNull('data_hora')->cursor()->each(function ($agenda) {
            $dateTime = \Carbon\Carbon::parse($agenda->data_hora);
            DB::table('agendas')->where('id', $agenda->id)->update([
                'data' => $dateTime->toDateString(),
                'hora_inicio' => $dateTime->toTimeString(),
            ]);
        });

        Schema::table('agendas', function (Blueprint $table) {
            // Remove a coluna antiga
            $table->dropColumn('data_hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dateTime('data_hora')->nullable()->after('assunto');
        });

        // (Opcional) Repopula a coluna antiga se necessário
        DB::table('agendas')->whereNotNull('data')->cursor()->each(function ($agenda) {
            $dateTime = \Carbon\Carbon::parse($agenda->data . ' ' . $agenda->hora_inicio);
            DB::table('agendas')->where('id', $agenda->id)->update([
                'data_hora' => $dateTime,
            ]);
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn(['data', 'hora_inicio', 'hora_fim']);
        });
    }
};
