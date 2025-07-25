<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas_parceiras', function (Blueprint $table) {
            $table->id();
            $table->string('nome_empresa');
            $table->string('contato_principal')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('ramo_atividade')->nullable();
            $table->decimal('horas_contratadas', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas_parceiras');
    }
};