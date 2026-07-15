<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;

class TrashController extends Controller
{
    // ゴミ箱一覧
    public function index()
    {
        // ★テーブル設計の見直しにより、1件の薬＝1行になったので、
        //   以前のような medicine_name によるグループ化は不要になった。
        $trashedMedicines = Medicine::onlyTrashed()->with(['patient' => function ($query) {
            $query->withTrashed();
        }])->get();
        $trashedPatients = Patient::onlyTrashed()->get();

        return view('trash.index', compact('trashedMedicines', 'trashedPatients'));
    }

    // 復元
    public function restore($id)
    {
        $medicine = Medicine::onlyTrashed()->find($id);
        if ($medicine) {
            $medicine->restore();

            return redirect()->route('trash.index')->with('success', 'お薬を復元しました。');
        }
        $patient = Patient::onlyTrashed()->find($id);
        if ($patient) {
            $patient->restore();
            // 関連する薬も一緒に復元する
            Medicine::onlyTrashed()
                ->where('patient_id', $patient->id)
                ->restore();

            return redirect()->route('trash.index')->with('success', '患者を復元しました。');
        }

        return redirect()->route('trash.index');
    }

    // 完全削除
    public function forceDelete($id)
    {
        $medicine = Medicine::onlyTrashed()->find($id);
        if ($medicine) {
            // 完全削除すると、紐づくmedicine_schedules・adherencesもDB側のcascade設定で自動的に削除される
            $medicine->forceDelete();

            return redirect()->route('trash.index')->with('success', 'お薬を完全に削除しました。');
        }

        $patient = Patient::onlyTrashed()->find($id);
        if ($patient) {
            // 関連する薬を先に完全削除
            Medicine::withTrashed()
                ->where('patient_id', $patient->id)
                ->forceDelete();
            // 患者を完全削除
            $patient->forceDelete();

            return redirect()->route('trash.index')->with('success', '患者を完全に削除しました。');
        }

        return redirect()->route('trash.index');
    }
}
