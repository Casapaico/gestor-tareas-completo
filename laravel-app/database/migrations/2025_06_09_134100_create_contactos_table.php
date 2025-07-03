<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // 4ta prueba
    // Create a migration class for creating the contactos table
    // This migration will create a table for managing contacts with fields for name, number, description, and active status
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {   // Create the contactos table, function receives a Blueprint instance to define the table structure
            $table->id();
            $table->string('nombre');
            $table->string('numero');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
    // fin de 4ta prueba
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
