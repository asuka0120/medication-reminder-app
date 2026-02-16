<style>
    body {
        background-color: #fdfaf2;
        color: #333333;
        font-family: "Hiragino Sans", "Meiryo", sans-serif;
        margin: 20px;
    }
    .calendar-card {
        background-color: #ffffff;
        border: 2px solid #d7ccc8;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        overflow-x: auto;
    }
    .timeline-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }
    .timeline-table th, .timeline-table td {
        border: 1px solid #d7ccc8;
        padding: 8px;
        text-align: center;
    }
    .medicine-column {
        background-color: #efebe9;
        position: sticky;
        left: 0;
        z-index: 10;
        text-align: left !important;
        min-width: 160px;
        font-weight: bold;
    }
    .today-highlight {
        background-color: #fff9c4 !important;
        border: 2px solid #fbc02d !important;
    }
    .check-box {
        font-size: 1.4em;
        color: #2e7d32;
    }
</style>

<h1>{{ $patient->name }} さんの服用履歴</h1>

<div class="calendar-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="?month={{ $prevMonth }}" style="text-decoration: none; color: #1976D2; font-weight: bold;">◀ 前の月</a>
        <h2 style="margin: 0; color: #4e342e;">{{ $currentYear }}年 {{ $currentMonth }}月</h2>
        <a href="?month={{ $nextMonth }}" style="text-decoration: none; color: #1976D2; font-weight: bold;">次の月 ▶</a>
    </div>

    <table class="timeline-table">
        <thead>
            <tr>
                <th class="medicine-column">お薬名 / 日付</th>
                @foreach($dates as $date)
                    <th style="background-color: #efebe9;" class="{{ $date->isToday() ? 'today-highlight' : '' }}">
                        {{ $date->format('d') }}<br>
                        <small style="color: {{ $date->isSunday() ? '#d32f2f' : ($date->isSaturday() ? '#1976d2' : 'inherit') }}">
                            ({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})
                        </small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($patient->medicines as $medicine)
                <tr>
                    <td class="medicine-column">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($medicine->image_path)
                                <img src="{{ asset('storage/' . $medicine->image_path) }}" style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px;">
                            @endif
                            <div>
                                <div style="font-size: 0.9em;">{{ $medicine->medicine_name }}</div>
                                <div style="font-size: 0.7em; color: #666;">{{ \Carbon\Carbon::parse($medicine->scheduled_time)->format('H:i') }}</div>
                            </div>
                        </div>
                    </td>
                    @foreach($dates as $date)
                        @php
                            $adherence = $medicine->adherences->where('taken_date', $date->toDateString())->first();
                        @endphp
                        <td class="{{ $date->isToday() ? 'today-highlight' : '' }}">
                            @if($adherence)
                                <div class="check-box">✅</div>
                                @if($adherence->note)
                                    <div style="font-size: 0.7em; color: #666; line-height: 1.1;">{{ $adherence->note }}</div>
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<a href="/patients" style="display: inline-block; margin-top: 25px; color: #ffffff; background-color: #5d4037; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: bold;">← 一覧に戻る</a>