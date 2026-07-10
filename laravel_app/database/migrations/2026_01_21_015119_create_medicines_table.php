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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained(); // 誰の薬か
            $table->string('medicine_name');               // 薬の名前
            $table->time('scheduled_time');                // 服用時刻（プッシュ通知の基準）
            $table->string('dosage')->nullable();          // 1回2錠、など
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
