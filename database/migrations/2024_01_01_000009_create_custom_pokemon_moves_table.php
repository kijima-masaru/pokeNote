<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_pokemon_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_pokemon_id')->constrained('custom_pokemon')->cascadeOnDelete();
            $table->foreignId('move_id')->constrained('moves')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->unique(['custom_pokemon_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_pokemon_moves');
    }
};
