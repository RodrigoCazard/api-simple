<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración y crea la tabla de usuarios.
     */
    public function up(): void
    {
        // Una migración describe la estructura de la base de forma versionada.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('rol', 20)->default('usuario');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración y elimina la tabla de usuarios.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
