<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->text('mensaje_personalizado')->nullable()->after('descripcion');
            $table->string('imagen_adjunta')->nullable()->after('mensaje_personalizado');
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn(['mensaje_personalizado', 'imagen_adjunta']);
        });
    }
};