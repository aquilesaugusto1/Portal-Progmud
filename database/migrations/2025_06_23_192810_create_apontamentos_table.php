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
            $table->unsignedBigInteger('agenda_id');
            $table->unsignedBigInteger('consultor_id');
            $table->decimal('horas_gastas', 5, 2);
            $table->text('descricao');
            $table->boolean('faturado')->default(false);
            $table->date('data_apontamento');
            $table->timestamps();

            $table->foreign('agenda_id')->references('id')->on('agendas')->onDelete('cascade');
            $table->foreign('consultor_id')->references('id')->on('consultores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apontamentos');
    }
};