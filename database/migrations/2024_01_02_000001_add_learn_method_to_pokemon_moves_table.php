<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pokemon_moves', function (Blueprint $table) {
            $table->string('learn_method')->default('level-up')->after('move_id');
            $table->unsignedSmallInteger('level_learned')->nullable()->after('learn_method');
        });
    }

    public function down(): void
    {
        Schema::table('pokemon_moves', function (Blueprint $table) {
            $table->dropColumn(['learn_method', 'level_learned']);
        });
    }
};
