<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultor_tech_lead', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultor_id');
            $table->unsignedBigInteger('tech_lead_id');
            $table->timestamps();

            $table->foreign('consultor_id')->references('id')->on('consultores')->onDelete('cascade');
            $table->foreign('tech_lead_id')->references('id')->on('usuarios')->onDelete('cascade');

            $table->unique(['consultor_id', 'tech_lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultor_tech_lead');
    }
};