<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_id')->constrained('battles')->cascadeOnDelete();
            $table->unsignedSmallInteger('turn_number');
            $table->foreignId('my_pokemon_id')->nullable()->constrained('custom_pokemon')->nullOnDelete();
            $table->string('opponent_pokemon_name', 50)->nullable();
            $table->foreignId('my_move_id')->nullable()->constrained('moves')->nullOnDelete();
            $table->foreignId('opponent_move_id')->nullable()->constrained('moves')->nullOnDelete();
            $table->unsignedTinyInteger('my_hp_remaining')->nullable();
            $table->unsignedTinyInteger('opponent_hp_remaining')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['battle_id', 'turn_number']);
            $table->index('battle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turns');
    }
};
