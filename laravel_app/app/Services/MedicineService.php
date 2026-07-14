<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineService
{
    /**
     * お薬を新規に一括登録する。
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

        foreach ($selectedTimes as $time) {
            Medicine::create([
                'patient_id' => $request->patient_id,
                'medicine_name' => $request->medicine_name,
                'dosage' => $dosage,
                'scheduled_time' => $time,
                'image_path' => $imagePath,
            ]);
        }

        return true;
    }

    /**
     * 既存のお薬スケジュールを、いったん削除してから新しい内容で作り直す。
     * 有効な服用時刻が1つもない場合はfalseを返す。
     */
    public function updateSchedule(Medicine $medicine, Request $request): bool
    {
        $selectedTimings = $this->filterValidTimes($request->timings);

        if (empty($selectedTimings)) {
            return false;
        }

        $dosage = $this->resolveDosage($request);
        $imagePath = $medicine->image_path;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('medicines', 'public');
        }

        // 古いスケジュールを一度削除
        Medicine::where('patient_id', $medicine->patient_id)
            ->where('medicine_name', $medicine->medicine_name)
            ->delete();

        // 新しく作成
        foreach ($selectedTimings as $time) {
            Medicine::create([
                'patient_id' => $medicine->patient_id,
                'medicine_name' => $request->medicine_name,
                'scheduled_time' => $time,
                'dosage' => $dosage,
                'image_path' => $imagePath,
            ]);
        }

        return true;
    }

    /**
     * 薬名が同じスケジュールをすべてゴミ箱に移動する（ソフトデリート）。
     */
    public function deleteSchedule(Medicine $medicine): void
    {
        Medicine::where('patient_id', $medicine->patient_id)
            ->where('medicine_name', $medicine->medicine_name)
            ->delete();
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
