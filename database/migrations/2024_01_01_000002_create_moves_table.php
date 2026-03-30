<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->id();
            $table->string('name_ja', 50)->unique();
            $table->string('name_en', 50)->unique();
            $table->string('type', 20);
            $table->string('category', 20);
            $table->unsignedSmallInteger('power')->nullable();
            $table->unsignedTinyInteger('accuracy')->nullable();
            $table->unsignedTinyInteger('pp');
            $table->tinyInteger('priority')->default(0);
            $table->text('description')->nullable();
            $table->boolean('makes_contact')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moves');
    }
};
