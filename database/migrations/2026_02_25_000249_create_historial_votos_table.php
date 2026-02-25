<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historial_votos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
        $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
        $table->timestamps();

        // Evita que un mismo vecino vote dos veces en la misma encuesta
        $table->unique(['usuario_id', 'encuesta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_votos');
    }
};
