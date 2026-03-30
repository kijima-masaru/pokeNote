<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_pokemon', function (Blueprint $table) {
            $table->id();
            $table->string('nickname', 50)->nullable();
            $table->foreignId('pokemon_id')->constrained('pokemon');
            $table->foreignId('ability_id')->constrained('abilities');
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('nature', 20);
            $table->unsignedTinyInteger('level')->default(50);
            $table->unsignedTinyInteger('iv_hp')->default(31);
            $table->unsignedTinyInteger('iv_attack')->default(31);
            $table->unsignedTinyInteger('iv_defense')->default(31);
            $table->unsignedTinyInteger('iv_sp_attack')->default(31);
            $table->unsignedTinyInteger('iv_sp_defense')->default(31);
            $table->unsignedTinyInteger('iv_speed')->default(31);
            $table->unsignedSmallInteger('ev_hp')->default(0);
            $table->unsignedSmallInteger('ev_attack')->default(0);
            $table->unsignedSmallInteger('ev_defense')->default(0);
            $table->unsignedSmallInteger('ev_sp_attack')->default(0);
            $table->unsignedSmallInteger('ev_sp_defense')->default(0);
            $table->unsignedSmallInteger('ev_speed')->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index('pokemon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_pokemon');
    }
};
