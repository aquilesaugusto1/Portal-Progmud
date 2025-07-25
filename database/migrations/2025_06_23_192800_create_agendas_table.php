<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('data_hora');
            $table->string('assunto');
            $table->string('status');
            $table->unsignedBigInteger('consultor_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('consultor_id')->references('id')->on('consultores')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas_parceiras')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};