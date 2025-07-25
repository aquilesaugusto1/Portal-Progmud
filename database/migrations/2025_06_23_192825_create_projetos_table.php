<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_projeto');
            $table->unsignedBigInteger('empresa_parceira_id');
            $table->enum('tipo', ['ams', 'act', 'act+']);
            $table->timestamps();

            $table->foreign('empresa_parceira_id')->references('id')->on('empresas_parceiras')->onDelete('cascade');
        });

        Schema::create('projeto_consultor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projeto_id');
            $table->unsignedBigInteger('consultor_id');
            $table->foreign('projeto_id')->references('id')->on('projetos')->onDelete('cascade');
            $table->foreign('consultor_id')->references('id')->on('consultores')->onDelete('cascade');
            $table->unique(['projeto_id', 'consultor_id']);
        });

        Schema::create('projeto_tech_lead', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projeto_id');
            $table->unsignedBigInteger('tech_lead_id');
            $table->foreign('projeto_id')->references('id')->on('projetos')->onDelete('cascade');
            $table->foreign('tech_lead_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->unique(['projeto_id', 'tech_lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_tech_lead');
        Schema::dropIfExists('projeto_consultor');
        Schema::dropIfExists('projetos');
    }
};