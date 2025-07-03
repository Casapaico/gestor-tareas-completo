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
        Schema::table('tareas', function (Blueprint $table) {
            // 4ta prueba
            // Add a foreign key column to the tareas table that references the contactos table
            // This column will be nullable and will set to null if the referenced contacto is deleted
            $table->foreignId('contacto_id')->nullable()->constrained('contactos')->onDelete('set null');
            // fin de 4ta prueba    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // 4ta prueba
            // Drop the foreign key constraint and the contacto_id column from the tareas table
            // This will remove the relationship between tareas and contactos
            $table->dropForeign(['contacto_id']);
            $table->dropColumn('contacto_id');
            // fin de 4ta prueba
        });
    }
};
