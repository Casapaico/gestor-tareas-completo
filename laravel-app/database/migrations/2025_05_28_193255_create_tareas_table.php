<?php

use Illuminate\Database\Migrations\Migration; // Import Migration class for creating databse migrations
use Illuminate\Database\Schema\Blueprint; // Import Blueprint class for defining the structure of the databse table
use Illuminate\Support\Facades\Schema; // Import Schema facade for interacting with the database schema

// Create a migration class for creating the tareas table
// This migration will create a table for managing tasks with fields for title, description, date/time, and completion status
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void // Create the tareas table
    // This method is called when the migration is run
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id(); // The primary key of the table, auto-incrementing ID
            $table->string('titulo'); // The title of the task, a string field
            $table->text('descripcion')->nullable(); // The description of the task, a text field that can be null
            $table->dateTime('fecha_hora'); // The date and time of the task, a dateTime field
            $table->boolean('completado')->default(false); // The completion status of the task, a boolean field with a default value of false
            $table->timestamps(); // Timestamps for created_at and updated_at, automatically managed by Laravel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void // Drop the tareas table
    // This method is called when the migration is rolled back
    {
        Schema::dropIfExists('tareas'); // Drop the tareas table if it exists
    }
};
