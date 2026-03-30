<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->string('opponent_name', 50)->nullable();
            $table->string('result', 10)->nullable();
            $table->string('format', 50)->nullable();
            $table->text('memo')->nullable();
            $table->dateTime('played_at')->nullable();
            $table->timestamps();

            $table->index('played_at');
            $table->index('result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
