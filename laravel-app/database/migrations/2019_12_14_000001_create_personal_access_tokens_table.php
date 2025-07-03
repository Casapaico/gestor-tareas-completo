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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

// This migration creates the personal_access_tokens table for managing personal access tokens in Laravel applications.
// The table includes fields for the tokenable entity, token name, unique token string, abilities, last used time, expiration time, and timestamps for creation and updates.
// The `morphs` method creates two columns: `tokenable_id` and `tokenable_type`, allowing the table to be used with multiple models.
// The `token` field is a unique string of 64 characters, ensuring that each token is distinct.
// The `abilities` field is optional and can store the permissions associated with the token.
// The `last_used_at` field records the last time the token was used, and `expires_at` indicates when the token will expire.
// Authentication and authorization systems can use this table to manage user sessions and API access securely.