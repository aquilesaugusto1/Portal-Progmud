<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contrato_cp_totvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained()->onDelete('cascade');
            $table->foreignId('cp_totvs_id')->constrained('cp_totvs')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['contrato_id', 'cp_totvs_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_cp_totvs');
    }
};