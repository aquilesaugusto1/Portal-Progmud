<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apontamentos', function (Blueprint $table) {
            $table->string('status')->default('Pendente')->after('horas_gastas');
            $table->string('caminho_anexo')->nullable()->after('descricao');
            $table->foreignId('aprovado_por')->nullable()->constrained('usuarios')->onDelete('set null')->after('caminho_anexo');
            $table->text('motivo_rejeicao')->nullable()->after('aprovado_por');
            $table->timestamp('data_aprovacao')->nullable()->after('motivo_rejeicao');
        });
    }

    public function down(): void
    {
        Schema::table('apontamentos', function (Blueprint $table) {
            $table->dropForeign(['aprovado_por']);
            $table->dropColumn(['status', 'caminho_anexo', 'aprovado_por', 'motivo_rejeicao', 'data_aprovacao']);
        });
    }
};
