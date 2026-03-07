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
    Schema::create('password_reset_codes', function (Blueprint $table) {
        $table->id();
        $table->string('email')->index(); // El correo al que enviamos el código
        $table->string('code');          // El código de 6 dígitos
        $table->timestamp('created_at')->nullable(); // Para validar la expiración (15 min)
    });
}
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};
