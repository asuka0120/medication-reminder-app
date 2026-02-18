<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Adherence;

class MedicineController extends Controller
{
    /**
     * お薬登録画面を表示する
     */
    public function create(Request $request)
    {
        $patient = Patient::findOrFail($request->patient_id);
        return view('medicines.create', compact('patient'));
    }

    /**
     * お薬データを保存する
     */
    public function store(Request $request)
    {
        // 分量の決定（選択肢 or 手入力）
    $dosage = $request->dosage_select === 'other' ? $request->dosage_manual : $request->dosage_select;

    // 写真の保存
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('medicines', 'public');
    }

    // チェックされた時刻を取得（未選択ならデフォルトで 09:00 にするなどの配慮）
    $selectedTimes = $request->input('times', ['09:00']);

    // 時刻の数だけ保存を繰り返す（これが一括登録！）
    foreach ($selectedTimes as $time) {
        \App\Models\Medicine::create([
            'patient_id'    => $request->patient_id,
            'medicine_name' => $request->medicine_name,
            'dosage'        => $dosage,
            'scheduled_time'=> $time,
            'image_path'    => $imagePath,
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
        return view('medicines.edit', compact('medicine'));
    }

    /**
     * お薬情報を更新する
     */
   public function update(Request $request, $id)
{
    // 編集対象のデータを一つ特定する（基準点として使用）
    $medicine = Medicine::findOrFail($id);

    // 1. バリデーション
    $request->validate([
        'medicine_name' => 'required|string|max:255',
        'timings' => 'required|array', 
        'dosage_select' => 'required',
        'image' => 'nullable|image|max:2048',
    ]);

    // 2. 分量の決定
    $dosage = $request->dosage_select === 'other' ? $request->dosage_manual : $request->dosage_select;

    // 3. 画像の処理
    $imagePath = $medicine->image_path;
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        $imagePath = $request->file('image')->store('medicines', 'public');
    }

    // 4. 【重要】同じ患者さんの、同じ名前のお薬スケジュールを一度すべて削除
    Medicine::where('patient_id', $medicine->patient_id)
            ->where('medicine_name', $medicine->medicine_name)
            ->delete();

    // 5. チェックされた時間（timings）の数だけ新規作成
    foreach ($request->timings as $time) {
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
        Adherence::create([
            'medicine_id' => $request->medicine_id,
            'taken_date' => now()->toDateString(),
            'note' => $request->note,
        ]);

        return redirect()->back();
    }

    /**
     * 「取消」ボタン：今日の服用記録を削除する
     */
    public function cancel(Request $request)
    {
        Adherence::where('medicine_id', $request->medicine_id)
            ->where('taken_date', now()->toDateString())
            ->delete();

        return redirect()->back();
    }

    /**
     * お薬をゴミ箱に移動する（ソフトデリート）
     */
    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete(); 
        
        return back()->with('success', 'お薬をゴミ箱に移動しました。');
    }

    /**
     * ゴミ箱に入っているお薬だけを表示する
     */
    public function trash()
    {
        $trashedMedicines = Medicine::onlyTrashed()->with('patient')->get();
        return view('medicines.trash', compact('trashedMedicines'));
    }

    /**
     * お薬をゴミ箱から元に戻す
     */
    public function restore($id)
    {
        $medicine = Medicine::withTrashed()->findOrFail($id);
        $medicine->restore();
        
        return redirect()->route('medicines.trash')->with('success', 'お薬を復元しました。');
    }

    /**
     * データベースから完全に削除する
     */
    public function forceDelete($id)
    {
        $medicine = Medicine::withTrashed()->findOrFail($id);
        $medicine->forceDelete();
        
        return redirect()->route('medicines.trash')->with('success', 'お薬を完全に削除しました。');
    }
}