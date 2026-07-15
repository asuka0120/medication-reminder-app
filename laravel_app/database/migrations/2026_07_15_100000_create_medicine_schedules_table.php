<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 「薬本体」と「服用スケジュール（時刻）」を分離するための新しいテーブル。
     * 1つの medicines レコードに対し、複数の medicine_schedules レコードがぶら下がる。
     */
    public function up(): void
    {
        Schema::create('medicine_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade'); // どの薬のスケジュールか
            $table->time('scheduled_time'); // 服用時刻（プッシュ通知の基準）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_schedules');
    }
};
