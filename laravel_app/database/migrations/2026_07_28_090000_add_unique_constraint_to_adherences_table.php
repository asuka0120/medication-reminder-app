<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 「同じ服薬スケジュール（medicine_schedule_id）・同じ日付（taken_date）」の
     * 服薬記録が重複登録されるのを防ぐため、ユニーク制約を追加する。
     *
     * すでに重複データが存在するとユニーク制約の追加自体が失敗するため、
     * 先に重複グループごとへ一番古い1件だけを残してクリーンアップしてから制約を付ける。
     */
    public function up(): void
    {
        // 1. 既存の重複データをクリーンアップする
        $duplicateGroups = DB::table('adherences')
            ->select('medicine_schedule_id', 'taken_date')
            ->whereNotNull('medicine_schedule_id')
            ->groupBy('medicine_schedule_id', 'taken_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $ids = DB::table('adherences')
                ->where('medicine_schedule_id', $group->medicine_schedule_id)
                ->where('taken_date', $group->taken_date)
                ->orderBy('id')
                ->pluck('id');

            // 一番古い（idが最小の）1件だけ残し、残りは削除する
            $idsToDelete = $ids->slice(1);

            if ($idsToDelete->isNotEmpty()) {
                DB::table('adherences')->whereIn('id', $idsToDelete)->delete();
            }
        }

        // 2. ユニーク制約を追加する
        Schema::table('adherences', function (Blueprint $table) {
            $table->unique(['medicine_schedule_id', 'taken_date'], 'adherences_schedule_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adherences', function (Blueprint $table) {
            $table->dropUnique('adherences_schedule_date_unique');
        });
    }
};
