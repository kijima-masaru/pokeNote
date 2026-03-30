<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->foreignId('move_id')->constrained('moves')->cascadeOnDelete();
            $table->unique(['pokemon_id', 'move_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_moves');
    }
};
