<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apontamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultor_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('agenda_id')->constrained('agendas')->onDelete('cascade');
            $table->decimal('horas_trabalhadas', 8, 2);
            $table->text('descricao_atividades')->nullable();
            $table->string('status_aprovacao')->default('pendente');
            $table->foreignId('aprovado_por')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('aprovado_em')->nullable();
            $table->text('comentarios_aprovacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apontamentos');
    }
};