<?php

namespace App\Http\Controllers;

class ReportController extends Controller
{
    /**
     * ログインユーザーの患者全員について、直近1週間分の服薬レポートを表示する
     */
    public function index()
    {
        // 6日前〜今日までの7日分の日付を、古い日付から順に並べる
        $dates = collect(range(6, 0))
            ->map(fn ($daysAgo) => now()->subDays($daysAgo)->toDateString());

        // ログインユーザーの患者と、その期間の服薬記録だけをまとめて読み込む
        // （全期間を読み込むと、記録が増えるほど無駄が多くなるため）
        $patients = auth()->user()->patients()
            ->with(['medicines' => function ($query) use ($dates) {
                $query->with(['adherences' => function ($query) use ($dates) {
                    $query->whereIn('taken_date', $dates);
                }]);
            }])
            ->get();

        return view('reports.index', compact('patients', 'dates'));
    }
}
