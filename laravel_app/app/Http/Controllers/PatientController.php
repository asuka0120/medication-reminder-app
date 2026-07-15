<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * 患者一覧を表示する
     */
    public function index()
    {
        // ログインしているユーザーの患者さんだけを取得する
        $patients = auth()->user()->patients()->with('medicines.schedules')->get();

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
    public function store(StorePatientRequest $request)
    {
        // バリデーションはStorePatientRequestに集約済み。

        $patient = new Patient;
        $patient->user_id = auth()->id(); // ログインユーザーのIDを紐づける
        $patient->name = $request->name;
        $patient->nickname = $request->nickname;
        $patient->memo = $request->memo;
        $patient->save();

        return redirect()->route('patients.index');
    }

    /**
     * 個別の服用履歴（横一行カレンダー）を表示する
     */
    public function show(Request $request, Patient $patient)
    {
        // ログインユーザーの患者かどうか確認する（他人のデータは見せない）
        $this->authorize('view', $patient);

        // 1. 表示したい月を取得（指定がなければ今月）
        $monthParam = $request->query('month', now()->format('Y-m'));
        $date = Carbon::parse($monthParam.'-01');

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // 2. 前月・次月のリンク用変数を準備
        $currentYear = $date->year;
        $currentMonth = $date->month;
        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        // 3. お薬と、その期間の服用記録を読み込む
        $patient->load(['medicines.schedules.adherences' => function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('taken_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
        }]);

        // 4. カレンダーのヘッダーに並べる「日付オブジェクト」の配列を作成
        $dates = [];
        for ($i = 0; $i < $date->daysInMonth; $i++) {
            $dates[] = $startOfMonth->copy()->addDays($i);
        }

        // 5. ビューに必要な変数をすべて渡す
        return view('patients.show', compact(
            'patient',      // 患者データ
            'dates',        // カレンダーに表示する日付の配列
            'currentYear',  // 表示中の年
            'currentMonth', // 表示中の月
            'prevMonth',    // 前月のリンク用（例：2026-04）
            'nextMonth'     // 次月のリンク用（例：2026-06）
        ));
    }

    /**
     * 患者情報の編集画面を表示する
     */
    public function edit(string $id)
    {
        $patient = Patient::findOrFail($id);

        // ログインユーザーの患者かどうか確認する
        $this->authorize('update', $patient);

        return view('patients.edit', compact('patient'));
    }

    /**
     * 患者情報を更新する
     */
    public function update(UpdatePatientRequest $request, string $id)
    {
        $patient = Patient::findOrFail($id);

        // バリデーションと認可（他人の患者は更新できないかの確認）は
        // UpdatePatientRequestに集約済み。

        $patient->name = $request->name;
        $patient->nickname = $request->nickname;
        $patient->memo = $request->memo;
        $patient->save();

        return redirect()->route('patients.index')->with('success', '家族情報を更新しました。');
    }

    /**
     * 患者データを削除する
     */
    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);

        // ログインユーザーの患者かどうか確認する
        $this->authorize('delete', $patient);

        // 関連する薬をソフトデリート（ゴミ箱に移動）
        $patient->medicines()->delete();

        // 患者をソフトデリート（ゴミ箱に移動）
        $patient->delete();

        return redirect()->route('patients.index')->with('success', '家族の登録を解除しました。');
    }
}
