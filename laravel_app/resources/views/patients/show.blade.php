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
        cursor: pointer; /* タップできることを示す */
    }
    .medicine-thumb {
        width: 35px; 
        height: 35px; 
        object-fit: cover; 
        border-radius: 4px; 
        cursor: pointer; /* タップできることを示す */
    }

    /* 拡大モーダルのスタイル */
    #big-display-area {
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.8); 
        z-index: 9999; 
        cursor: pointer;
    }
    .modal-content {
        background: white;
        width: 90%;
        max-width: 450px;
        margin: 50px auto;
        padding: 20px;
        border-radius: 20px;
        text-align: center;
        position: relative;
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
                                {{-- 写真タップで拡大 --}}
                                <img src="{{ asset('storage/' . $medicine->image_path) }}" 
                                     class="medicine-thumb"
                                     onclick="showBigDisplay('お薬の写真', '{{ $medicine->medicine_name }}', '{{ asset('storage/' . $medicine->image_path) }}', '', true)">
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
                        <td class="{{ $date->isToday() ? 'today-highlight' : '' }}"
    style="{{ (!$adherence && $date->isPast() && !$date->isToday()) ? 'background-color: #ffebee;' : '' }}">
    @if($adherence)
        <div class="check-box" onclick="showBigDisplay('{{ $date->format('n月j日') }} {{ $adherence->taken_time ? \Carbon\Carbon::parse($adherence->taken_time)->format('H:i') : \Carbon\Carbon::parse($medicine->scheduled_time)->format('H:i') }}', '{{ $medicine->medicine_name }}', '{{ $medicine->image_path ? asset('storage/' . $medicine->image_path) : '' }}', '{{ addslashes($adherence->note) }}')">✅</div>
        @if($adherence->note)
            <div style="font-size: 0.7em; color: #666; line-height: 1.1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ Str::limit($adherence->note, 10, '…') }}</div>
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

<div id="big-display-area" onclick="hideBigDisplay()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <h2 id="modal-time" style="margin: 0; color: #5d4037; font-size: 1.2em;"></h2>
        <h3 id="modal-name" style="margin: 10px 0; font-size: 1.6em;"></h3>
        
        <img id="modal-image" src="" style="width: 100%; max-height: 300px; object-fit: contain; border-radius: 10px; margin-bottom: 15px; display: none;">
        
        <div id="modal-note-box" style="background: #fff8e1; padding: 15px; border-radius: 10px; border: 1px solid #ffe082; text-align: left;">
            <p style="font-weight: bold; margin: 0 0 5px 0; color: #795548; font-size: 0.9em;">📝 体調メモ</p>
            <p id="modal-note" style="margin: 0; font-size: 1.1em; min-height: 1.2em;"></p>
        </div>
        
        <button onclick="hideBigDisplay()" style="margin-top: 20px; padding: 10px 30px; border-radius: 25px; border: none; background: #5d4037; color: white; font-weight: bold; cursor: pointer;">閉じる</button>
    </div>
</div>

<script>
function showBigDisplay(time, name, imagePath, note, isPhotoOnly = false) {
    document.getElementById('modal-time').innerText = isPhotoOnly ? time : time + ' の記録';
    document.getElementById('modal-name').innerText = name;
    
    const noteBox = document.getElementById('modal-note-box');
    if (isPhotoOnly) {
        noteBox.style.display = 'none';
    } else {
        noteBox.style.display = 'block';
        document.getElementById('modal-note').innerText = note ? note : '（メモはありません）';
    }
    
    const imgTag = document.getElementById('modal-image');
    if (imagePath) {
        imgTag.src = imagePath;
        imgTag.style.display = 'block';
    } else {
        imgTag.style.display = 'none';
    }
    document.getElementById('big-display-area').style.display = 'block';
}

function hideBigDisplay() {
    document.getElementById('big-display-area').style.display = 'none';
}
</script>