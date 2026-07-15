<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Adherence;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\Patient;
use App\Services\MedicineService;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function __construct(private MedicineService $medicineService) {}

    /**
     * お薬登録画面を表示する
     */
    public function create(Request $request)
    {
        $patient = Patient::findOrFail($request->patient_id);

        // ログインユーザーの患者かどうか確認する（他人の患者は操作できない）
        $this->authorize('update', $patient);

        return view('medicines.create', compact('patient'));
    }

    /**
     * お薬データを保存する
     */
    public function store(StoreMedicineRequest $request)
    {
        // バリデーションと認可（他人の患者に薬を登録できないかの確認）は
        // StoreMedicineRequestに集約済み。ここに到達した時点で両方通過している。

        // 実際の登録処理（分量の決定・画像保存・時刻の一括登録）はMedicineServiceに委譲する
        $created = $this->medicineService->createSchedule($request);

        if (! $created) {
            return back()
                ->withInput()
                ->with('error_message', '服用時間が設定されていません。チェックボックスを選ぶか、自由な時間を入力してください。');
        }

        return redirect()->route('patients.index')->with('success', 'お薬を一括登録しました。');
    }

    /**
     * お薬編集画面を表示する
     */
    public function edit($id)
    {
        $medicine = Medicine::findOrFail($id);

        // ログインユーザーの患者の薬かどうか確認する（他人の薬は編集できない）
        $this->authorize('update', $medicine);

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

        // 実際の更新処理（古いスケジュール削除・新規作成）はMedicineServiceに委譲する
        $updated = $this->medicineService->updateSchedule($medicine, $request);

        if (! $updated) {
            return back()
                ->withInput()
                ->with('error_message', '服用時間が設定されていません。チェックボックスを選ぶか、自由な時間を入力してください。');
        }

        return redirect()->route('patients.index')->with('success', 'お薬情報を一括更新しました。');
    }

    /**
     * 「飲んだ！」ボタン：服用記録とメモを保存する
     */
    public function take(Request $request)
    {
        $schedule = MedicineSchedule::findOrFail($request->schedule_id);

        // ログインユーザーの患者の薬かどうか確認する
        $this->authorize('update', $schedule->medicine);

        Adherence::create([
            'medicine_id' => $schedule->medicine_id,
            'medicine_schedule_id' => $schedule->id,
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
        $schedule = MedicineSchedule::findOrFail($request->schedule_id);

        // ログインユーザーの患者の薬かどうか確認する
        $this->authorize('update', $schedule->medicine);

        Adherence::where('medicine_schedule_id', $schedule->id)
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
        $this->authorize('delete', $medicine);

        $this->medicineService->deleteSchedule($medicine);

        return back()->with('success', '「'.$medicine->medicine_name.'」の全スケジュールをゴミ箱に移動しました。');
    }
}
