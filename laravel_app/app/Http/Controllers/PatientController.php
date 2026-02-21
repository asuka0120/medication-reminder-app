<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Patient;

class PatientController extends Controller
{
    /**
     * 患者一覧を表示する
     */
    public function index()
    {
        // 患者さんと、その人に紐づくお薬（medicines）を一緒に取得する
        $patients = Patient::with('medicines')->get();
        
        return view('patients.index', compact('patients'));
    }

    /**
     * 新しい家族の登録画面を表示する
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * 家族データを保存する
     */
    public function store(Request $request)
    {
        $patient = new Patient();
        $patient->name = $request->name;
        $patient->nickname = $request->nickname;
        $patient->memo = $request->memo;
        $patient->save();

        return redirect('/patients');
    }

    /**
     * 個別の服用履歴（横一行カレンダー）を表示する
     */
    public function show(Request $request, Patient $patient)
    {
        // 1. 表示したい月を取得（指定がなければ今月）
        $monthParam = $request->query('month', now()->format('Y-m'));
        $date = Carbon::parse($monthParam . '-01');

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // 2. 前月・次月のリンク用変数を準備
        $currentYear = $date->year;
        $currentMonth = $date->month;
        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        // 3. お薬と、その期間の服用記録を読み込む
        $patient->load(['medicines.adherences' => function($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('taken_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
        }]);

        // 4. カレンダーのヘッダーに並べる「日付オブジェクト」の配列を作成
        $dates = [];
        for ($i = 0; $i < $date->daysInMonth; $i++) {
            $dates[] = $startOfMonth->copy()->addDays($i);
        }

        // 5. ビューに必要な変数をすべて渡す
        return view('patients.show', compact(
            'patient', 
            'dates', 
            'currentYear', 
            'currentMonth', 
            'prevMonth', 
            'nextMonth'
        ));
    }

    /**
     * 以降、必要に応じて実装（現在は空）
     */
    public function edit(string $id) { }
    public function update(Request $request, string $id) { }

    /**
     * 患者データを削除する
     */
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);

        // お父さんに紐づくすべてのお薬（ゴミ箱に入っているものも含めて）ループ
        foreach ($patient->medicines()->withTrashed()->get() as $medicine) {
            // 1. 孫（服用記録）を完全に消去
            $medicine->adherences()->forceDelete();
            // 2. 子（お薬）を完全に消去
            $medicine->forceDelete();
        }

        // 3. 最後に親（お父さん）を削除
        $patient->delete();

        return redirect()->route('patients.index')->with('success', '家族の登録を解除しました。');
    }
}