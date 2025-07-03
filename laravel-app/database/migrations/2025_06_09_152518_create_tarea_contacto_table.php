<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea_contacto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->onDelete('cascade');
            $table->foreignId('contacto_id')->constrained('contactos')->onDelete('cascade');
            $table->boolean('enviado')->default(false); // Para saber si ya se envió a este contacto
            $table->timestamp('enviado_at')->nullable(); // Cuándo se envió
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['tarea_id', 'contacto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_contacto');
    }
};