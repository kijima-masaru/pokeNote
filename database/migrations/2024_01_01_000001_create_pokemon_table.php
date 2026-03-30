<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('pokedex_number')->unique();
            $table->string('name_ja', 50);
            $table->string('name_en', 50);
            $table->string('form_name', 50)->nullable();
            $table->unsignedSmallInteger('base_hp');
            $table->unsignedSmallInteger('base_attack');
            $table->unsignedSmallInteger('base_defense');
            $table->unsignedSmallInteger('base_sp_attack');
            $table->unsignedSmallInteger('base_sp_defense');
            $table->unsignedSmallInteger('base_speed');
            $table->string('sprite_url', 255)->nullable();
            $table->timestamps();

            $table->index('name_ja');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};
