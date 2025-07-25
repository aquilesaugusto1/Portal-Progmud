<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apontamentos', function (Blueprint $table) {
            $table->time('hora_inicio')->nullable()->after('agenda_id');
            $table->time('hora_fim')->nullable()->after('hora_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('apontamentos', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fim']);
        });
    }
};
