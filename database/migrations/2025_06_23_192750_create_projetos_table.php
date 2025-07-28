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
            $table->foreignId('empresa_parceira_id')->constrained('empresas_parceiras')->onDelete('cascade');
            $table->foreignId('tech_lead_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projetos');
    }
};