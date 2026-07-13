<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Adherence;
use App\Models\Medicine;
use App\Models\Patient;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    /**
     * お薬登録画面を表示する
     */
    public function create(Request $request)
    {
        $patient = Patient::findOrFail($request->patient_id);

        // ログインユーザーの患者かどうか確認する（他人の患者は操作できない）
        abort_if($patient->user_id !== auth()->id(), 403);

        return view('medicines.create', compact('patient'));
    }

    /**
     * お薬データを保存する
     */
    public function store(StoreMedicineRequest $request)
    {
        // バリデーションと認可（他人の患者に薬を登録できないかの確認）は
        // StoreMedicineRequestに集約済み。ここに到達した時点で両方通過している。

        // 1. 分量の決定（選択肢 or 手入力）
        $dosage = $request->dosage_select === 'other' ? $request->dosage_manual : $request->dosage_select;

        // 2. 写真の保存
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('medicines', 'public');
        }

        // 3. 入力された時刻を取得
        $inputTimes = $request->input('times', []);

        // ★重要：自由入力欄で「空のまま」送信されたデータを除外する
        $selectedTimes = array_filter($inputTimes, function ($value) {
            return ! empty($value);
        });

        // ★重要：時刻が一つもない場合はエラーを返して中断する
        if (empty($selectedTimes)) {
            return back()
                ->withInput()
                ->with('error_message', '服用時間が設定されていません。チェックボックスを選ぶか、自由な時間を入力してください。');
        }

        // 5. 時刻の数だけ保存を繰り返す（一括登録）
        foreach ($selectedTimes as $time) {
            \App\Models\Medicine::create([
                'patient_id' => $request->patient_id,
                'medicine_name' => $request->medicine_name,
                'dosage' => $dosage,
                'scheduled_time' => $time,
                'image_path' => $imagePath,
            ]);
        }

        return redirect('/patients')->with('success', 'お薬を一括登録しました。');
    }

    /**
     * お薬編集画面を表示する
     */
    public function edit($id)
    {
        $medicine = Medicine::findOrFail($id);

        // ログインユーザーの患者の薬かどうか確認する（他人の薬は編集できない）
        abort_if($medicine->patient->user_id !== auth()->id(), 403);

        return view('medicines.edit', compact('medicine'));
    }

    /**
     * お薬情報を更新する
     */
    public function update(UpdateMedicineRequest $request, $id)
    {
        $medicine = Medicine::findOrFail($id);

        // バリデーションと認可（他人の薬は更新できないかの確認）は
        // UpdateMedicineRequestに集約済み。ここに到達した時点で両方通過している。

        // 2. 分量と画像の処理（現状のまま）
        $dosage = $request->dosage_select === 'other' ? $request->dosage_manual : $request->dosage_select;
        $imagePath = $medicine->image_path;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('medicines', 'public');
        }

        // 3. 空文字を除外して「有効な時間」だけを取り出す
        $selectedTimings = array_filter($request->timings, function ($value) {
            return ! empty($value);
        });

        // 4. 有効な時間が一つもない場合は差し戻す
        if (empty($selectedTimings)) {
            return back()
                ->withInput()
                ->with('error_message', '服用時間が設定されていません。チェックボックスを選ぶか、自由な時間を入力してください。');
        }

        // 5. 古いスケジュールを一度削除
        Medicine::where('patient_id', $medicine->patient_id)
            ->where('medicine_name', $medicine->medicine_name)
            ->delete();

        // 6. 新しく作成
        foreach ($selectedTimings as $time) {
            Medicine::create([
                'patient_id' => $medicine->patient_id,
                'medicine_name' => $request->medicine_name,
                'scheduled_time' => $time,
                'dosage' => $dosage,
                'image_path' => $imagePath,
            ]);
        }

        return redirect('/patients')->with('success', 'お薬情報を一括更新しました。');
    }

    /**
     * 「飲んだ！」ボタン：服用記録とメモを保存する
     */
    public function take(Request $request)
    {
        $medicine = Medicine::findOrFail($request->medicine_id);

        // ログインユーザーの患者の薬かどうか確認する
        abort_if($medicine->patient->user_id !== auth()->id(), 403);

        Adherence::create([
            'medicine_id' => $request->medicine_id,
            'taken_date' => now()->toDateString(),
            'taken_time' => now()->format('H:i:s'), // 実際に押した時刻を保存
            'note' => $request->note,
        ]);

        return redirect()->back();
    }

    /**
     * 「取消」ボタン：今日の服用記録を削除する
     */
    public function cancel(Request $request)
    {
        $medicine = Medicine::findOrFail($request->medicine_id);

        // ログインユーザーの患者の薬かどうか確認する
        abort_if($medicine->patient->user_id !== auth()->id(), 403);

        Adherence::where('medicine_id', $request->medicine_id)
            ->where('taken_date', now()->toDateString())
            ->delete();

        return redirect()->back();
    }

    /**
     * お薬に関連するすべてのスケジュールをゴミ箱に移動する（ソフトデリート）
     */
    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);

        // ログインユーザーの患者の薬かどうか確認する（他人の薬は削除できない）
        abort_if($medicine->patient->user_id !== auth()->id(), 403);

        Medicine::where('patient_id', $medicine->patient_id)
            ->where('medicine_name', $medicine->medicine_name)
            ->delete();

        return back()->with('success', '「'.$medicine->medicine_name.'」の全スケジュールをゴミ箱に移動しました。');
    }
}
