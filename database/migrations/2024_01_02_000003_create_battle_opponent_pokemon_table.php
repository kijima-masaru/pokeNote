<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battle_opponent_pokemon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_id')->constrained('battles')->cascadeOnDelete();
            $table->foreignId('pokemon_id')->nullable()->constrained('pokemon')->nullOnDelete();
            $table->tinyInteger('slot')->default(1); // 1-6
            $table->string('nickname', 50)->nullable();
            $table->timestamps();

            $table->unique(['battle_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_opponent_pokemon');
    }
};
