<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->boolean('is_mega')->default(false)->after('sprite_url');
            $table->unsignedInteger('base_pokemon_id')->nullable()->after('is_mega')
                  ->comment('メガシンカ元ポケモンのID');
        });
    }

    public function down(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn(['is_mega', 'base_pokemon_id']);
        });
    }
};
