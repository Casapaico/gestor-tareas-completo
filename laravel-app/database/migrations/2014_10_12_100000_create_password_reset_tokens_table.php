<?php

use Illuminate\Database\Migrations\Migration; // Import the Migration class for creating database migrations
use Illuminate\Database\Schema\Blueprint; // Import the Blueprint class for defining the structure of the database table
use Illuminate\Support\Facades\Schema; // Import the Schema facade for interacting with the database schema

return new class extends Migration // Create a migration class for creating the password reset tokens table
{
    /**
     * Run the migrations.
     */
    public function up(): void 
        // Create the password reset tokens table
        // This method is called when the migration is run
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) { // Create the password reset tokens table
            $table->string('email')->primary(); // The email address of the user requesting the password reset, set as primary key
            $table->string('token'); // The token used for password reset
            $table->timestamp('created_at')->nullable(); // The time when the token was created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
