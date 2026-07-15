<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineService
{
    /**
     * お薬本体を新規に登録し、選択された時刻の分だけスケジュールを作成する。
     * 有効な服用時刻が1つもない場合はfalseを返す。
     */
    public function createSchedule(Request $request): bool
    {
        $selectedTimes = $this->filterValidTimes($request->input('times', []));

        if (empty($selectedTimes)) {
            return false;
        }

        $dosage = $this->resolveDosage($request);
        $imagePath = $this->storeImageIfPresent($request);

        // 1. 薬本体を1件だけ作成する
        $medicine = Medicine::create([
            'patient_id' => $request->patient_id,
            'medicine_name' => $request->medicine_name,
            'dosage' => $dosage,
            'image_path' => $imagePath,
        ]);

        // 2. 選んだ時刻の数だけスケジュールを作成する
        foreach ($selectedTimes as $time) {
            $medicine->schedules()->create(['scheduled_time' => $time]);
        }

        return true;
    }

    /**
     * 薬本体の情報（薬名・分量・画像）を更新し、スケジュールを新しい内容で作り直す。
     * 有効な服用時刻が1つもない場合はfalseを返す。
     */
    public function updateSchedule(Medicine $medicine, Request $request): bool
    {
        $selectedTimings = $this->filterValidTimes($request->timings);

        if (empty($selectedTimings)) {
            return false;
        }

        // 1. 薬本体の情報を更新する
        $medicine->medicine_name = $request->medicine_name;
        $medicine->dosage = $this->resolveDosage($request);
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $medicine->image_path = $request->file('image')->store('medicines', 'public');
        }
        $medicine->save();

        // 2. 古いスケジュールを一度削除してから、新しい時刻で作り直す
        $medicine->schedules()->delete();
        foreach ($selectedTimings as $time) {
            $medicine->schedules()->create(['scheduled_time' => $time]);
        }

        return true;
    }

    /**
     * 薬本体をゴミ箱に移動する（ソフトデリート）。
     * 紐づくスケジュールは、薬本体が完全削除されるまでそのまま残る。
     */
    public function deleteSchedule(Medicine $medicine): void
    {
        $medicine->delete();
    }

    /**
     * 分量の決定（選択肢 or 手入力）
     */
    private function resolveDosage(Request $request): string
    {
        return $request->dosage_select === 'other' ? $request->dosage_manual : $request->dosage_select;
    }

    /**
     * 写真がアップロードされていれば保存し、保存先パスを返す。
     */
    private function storeImageIfPresent(Request $request): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('medicines', 'public');
        }

        return null;
    }

    /**
     * 空欄の時刻を除外し、有効な時刻だけを返す。
     */
    private function filterValidTimes(array $times): array
    {
        return array_filter($times, function ($value) {
            return ! empty($value);
        });
    }
}
