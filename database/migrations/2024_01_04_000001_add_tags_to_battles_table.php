<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            // カンマ区切りのタグ文字列として保存（例: "ランクマッチ,初手,受け崩し"）
            $table->string('tags', 500)->nullable()->after('memo');
        });
    }

    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
