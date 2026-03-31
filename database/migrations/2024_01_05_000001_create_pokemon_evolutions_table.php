<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon_evolutions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_pokemon_id');
            $table->unsignedBigInteger('to_pokemon_id');
            $table->string('method', 100)->nullable(); // level_up, use_item, trade, etc.
            $table->integer('min_level')->nullable();
            $table->string('trigger_item', 100)->nullable();
            $table->timestamps();

            $table->foreign('from_pokemon_id')->references('id')->on('pokemon')->onDelete('cascade');
            $table->foreign('to_pokemon_id')->references('id')->on('pokemon')->onDelete('cascade');
            $table->unique(['from_pokemon_id', 'to_pokemon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_evolutions');
    }
};
