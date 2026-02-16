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
        $medicine = Medicine::findOrFail($id);
        $medicine->medicine_name = $request->medicine_name;
        $medicine->scheduled_time = $request->scheduled_time;

        // ★ 更新時もプルダウン判定を適用
        if ($request->dosage_select === 'other') {
            $medicine->dosage = $request->dosage_manual;
        } else {
            $medicine->dosage = $request->dosage_select;
        }

        // 画像が新しくアップロードされた場合のみ上書き
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('medicines', 'public');
            $medicine->image_path = $path;
        }

        $medicine->save();

        return redirect('/patients')->with('success', 'お薬情報を更新しました。');
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