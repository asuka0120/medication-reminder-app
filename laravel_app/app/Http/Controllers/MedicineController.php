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
    $selectedTimes = array_filter($inputTimes, function($value) {
        return !empty($value);
    });

    // ★重要：時刻が一つもない場合はエラーを返して中断する
    if (empty($selectedTimes)) {
        return back()
            ->withInput() // 入力内容を保持したまま戻る
            ->with('error_message', '服用時間が設定されていません。チェックボックスを選ぶか、自由な時間を入力してください。');
    }

    // 5. 時刻の数だけ保存を繰り返す（一括登録）
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
    $medicine = Medicine::findOrFail($id);

    // 1. バリデーション
    $request->validate([
        'medicine_name' => 'required|string|max:255',
        'timings' => 'required|array', // ここで配列であることを確認
        'dosage_select' => 'required',
        'image' => 'nullable|image|max:2048',
    ]);

    // 2. 分量と画像の処理（現状のまま）
    $dosage = $request->dosage_select === 'other' ? $request->dosage_manual : $request->dosage_select;
    $imagePath = $medicine->image_path;
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        $imagePath = $request->file('image')->store('medicines', 'public');
    }

    // 3. 空文字を除外して「有効な時間」だけを取り出す
    $selectedTimings = array_filter($request->timings, function($value) {
        return !empty($value);
    });

    // 4. 有効な時間が一つもない場合は差し戻す
    if (empty($selectedTimings)) {
        return back()
            ->withInput()
            ->with('error_message', '服用時間が設定されていません。チェックボックスを選ぶか、自由な時間を入力してください。');
    }

    // --- ここからデータベース操作（トランザクション推奨ですが、まずはこのままでOK） ---

    // 5. 古いスケジュールを一度削除（患者IDと薬名を指定して、その薬の全スケジュールを消す）
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