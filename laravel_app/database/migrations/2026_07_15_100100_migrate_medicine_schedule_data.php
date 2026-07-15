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
     * 既存の medicines テーブルは「薬名＋時刻」で1行になっていたため、
     * 同じ薬（患者ID＋薬名が同じ）で複数行に分かれてしまっていた。
     * このマイグレーションで、
     *  1. 「薬名が同じグループ」ごとに1件の medicines レコード（代表行）へ集約
     *  2. 各行が持っていた scheduled_time を medicine_schedules レコードとして作成
     *  3. adherences が指していた medicine_id を、代表行のIDへ付け替えつつ、
     *     新しい medicine_schedule_id も設定する
     *  4. 不要になった重複行を削除し、medicines テーブルから scheduled_time 列を削除
     * を行う。
     *
     * ※ adherences.medicine_id 列はあえて物理削除しない。
     *   SQLite/MySQLで外部キー付き列の削除の作法が異なり、無理に統一しようとすると
     *   環境依存の不具合を生みやすいため、「以後アプリからは参照しない予備列」として残す。
     *   実質的な参照先は medicine_schedule_id に一本化する。
     */
    public function up(): void
    {
        // 1. adherences に新しい参照カラムを追加（まだ中身は空）
        Schema::table('adherences', function (Blueprint $table) {
            $table->foreignId('medicine_schedule_id')->nullable()->after('medicine_id');
        });

        // 2. 既存の medicines を「患者ID＋薬名」でグループ化して移行する
        //    ソフトデリート済みの行も含めて取得する（withTrashed相当）
        $medicines = DB::table('medicines')->orderBy('id')->get();

        $groups = $medicines->groupBy(function ($row) {
            return $row->patient_id.'|'.$row->medicine_name;
        });

        foreach ($groups as $rows) {
            // グループの中で一番古い行（id最小）を「代表の薬レコード」として残す
            $canonical = $rows->sortBy('id')->first();

            foreach ($rows as $row) {
                // 各行が持っていた時刻を、代表レコードにぶら下がる medicine_schedules として作成
                $scheduleId = DB::table('medicine_schedules')->insertGetId([
                    'medicine_id' => $canonical->id,
                    'scheduled_time' => $row->scheduled_time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // この行(旧medicine_id)を参照していたadherenceを、
                // 代表行のID(medicine_id)と、新しいスケジュールID(medicine_schedule_id)へ付け替える
                DB::table('adherences')
                    ->where('medicine_id', $row->id)
                    ->update([
                        'medicine_id' => $canonical->id,
                        'medicine_schedule_id' => $scheduleId,
                    ]);
            }

            // 代表行以外（重複していた行）はもう不要なので削除する
            // ※ adherences.medicine_id は上ですでに代表行のIDへ付け替え済みなので、
            //   外部キー制約に違反せず安全に削除できる
            $duplicateIds = $rows->pluck('id')->reject(fn ($id) => $id === $canonical->id);
            if ($duplicateIds->isNotEmpty()) {
                DB::table('medicines')->whereIn('id', $duplicateIds)->delete();
            }
        }

        // 3. medicine_schedule_id を今後の正式な参照として使えるよう外部キー制約を追加する
        Schema::table('adherences', function (Blueprint $table) {
            $table->foreign('medicine_schedule_id')->references('id')->on('medicine_schedules')->onDelete('cascade');
        });

        // 4. medicines テーブルからは scheduled_time 列が不要になったので削除する
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('scheduled_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 注意：一度統合した薬名の重複を元通りに復元することはできないため、
     * このロールバックはテーブル構造のみを元に戻す（データの完全な復元は保証しない）。
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable();
        });

        Schema::table('adherences', function (Blueprint $table) {
            $table->dropForeign(['medicine_schedule_id']);
            $table->dropColumn('medicine_schedule_id');
        });
    }
};
