<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon_abilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')->constrained('pokemon')->cascadeOnDelete();
            $table->foreignId('ability_id')->constrained('abilities')->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->unique(['pokemon_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_abilities');
    }
};
