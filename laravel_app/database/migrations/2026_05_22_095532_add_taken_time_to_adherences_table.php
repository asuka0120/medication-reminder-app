<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adherences', function (Blueprint $table) {
            // 実際に「飲んだ！」ボタンを押した時刻を保存する列を追加
            $table->time('taken_time')->nullable()->after('taken_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adherences', function (Blueprint $table) {
            $table->dropColumn('taken_time');
        });
    }
};