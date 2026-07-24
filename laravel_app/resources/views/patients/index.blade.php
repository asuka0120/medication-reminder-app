<style>
    body {
        background-color: #fdfaf2;
        color: #333333;
        font-family: "Hiragino Sans", "Meiryo", sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    .patient-card {
        background-color: #ffffff;
        border: 2px solid #d7ccc8;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #d7ccc8;
        border-radius: 8px;
        overflow: hidden;
    }
    th {
        background-color: #efebe9;
        color: #5d4037;
        padding: 12px;
        text-align: center;
    }
    td {
        /* background-color は個別指定するため削除 */
        padding: 12px;
        border-bottom: 1px solid #efebe9;
        vertical-align: middle;
    }
    .btn {
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    padding: 14px 15px;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    transition: 0.3s;
    }
    .btn-danger { background-color: #B71C1C; color: white; }
    .btn-edit { background-color: #7B1FA2; color: white; }

    /* スケジュールを中央寄せで配置するスタイル */
    .schedule-box {
        display: flex;
        align-items: center;
        justify-content: space-between; 
        gap: 40px;
        background-color: #ffffff;      /* ボックスの中は白で固定 */
        border: 1px solid #ddd;
        padding: 10px 20px;
        border-radius: 8px;
        margin: 10px auto;             /* 上下の余白を少し増やし、左右autoで中央寄せ */
        width: 350px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .scheduled-time {
        min-width: 60px;
        font-weight: bold;
        font-size: 1.2em;
    }

    /* お薬ごとの区切りと背景色の切り替え */
    .medicine-row {
        border-top: 3px solid #d7ccc8 !important; /* 薬の種類が変わる線を太く */
    }
    .bg-odd { background-color: #ffffff; }     /* 1つ目の薬：白 */
    .bg-even { background-color: #faf7f2; }    /* 2つ目の薬：ごく薄いベージュ */

    /* ...既存のスタイル... */
    
    .clickable { cursor: pointer; transition: opacity 0.2s; }
    .clickable:hover { opacity: 0.8; }

    /* 拡大モーダルのスタイル（show.blade.phpと同じ） */
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
    /* 飲み忘れ警告のスタイル */
    .alert-overdue {
        background-color: #ffebee !important; /* 薄い赤色 */
    }
    .text-overdue {
        color: #d32f2f !important;
        font-weight: bold;
        animation: blink 2s infinite; /* ゆるやかに点滅 */
    }
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
</style>

<h1>ご家族のための服薬管理（くすりサポート）</h1>

<div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
    <a href="{{ route('patients.create') }}" style="text-decoration: none; font-weight: bold; color: #1976D2;">＋ 新しく家族を登録する</a>
    <a href="{{ route('trash.index') }}" style="color: #607D8B; text-decoration: none;">[ 🗑 ゴミ箱を見る ]</a>
    <button id="notify-btn" onclick="toggleNotifications()" class="btn" style="background-color: #ff9800; color: white; min-width: 180px;">
    🔔 通知設定を確認中...
</button>
</div>

<hr style="border: 0; border-top: 1px solid #d7ccc8; margin: 20px 0;">

@foreach ($patients as $patient)
    <div class="patient-card">
        {{-- ▼ ここから修正：名前と削除ボタンを横並びに --}}
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin-top: 0; color: #4e342e;">
    {{ $patient->name }}{{ $patient->nickname ? '（' . $patient->nickname . '）' : '' }} さん
</h3>
            
    <div style="display: flex; gap: 10px; align-items: flex-start;">
    {{-- 編集ボタン --}}
    <a href="{{ route('patients.edit', $patient->id) }}" 
       style="background-color: #1565C0; color: white; padding: 14px 16px; border-radius: 6px; cursor: pointer; font-size: 1.1em; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-decoration: none; display: inline-block; line-height: normal;">
        ✏️ 編集
    </a>
    {{-- 登録解除ボタン --}}
    <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" onsubmit="return confirm('{{ $patient->name }}さんのデータをすべて削除して登録を解除しますか？');">
        @csrf
        @method('DELETE')
        <button type="submit" style="background-color: #B71C1C; color: white; border: none; padding: 14px 16px; border-radius: 6px; cursor: pointer; font-size: 1.1em; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            🗑 登録解除
        </button>
    </form>
</div>
        </div>
        {{-- ▲ ここまで --}}

        {{-- ▼ ここを追加：備考がある場合のみ表示する --}}
    @if($patient->memo)
        <div style="background-color: #fdf5e6; padding: 10px; border-radius: 8px; margin-bottom: 15px; border-left: 5px solid #d7ccc8; color: #5d4037;">
            <strong>📝 備考：</strong>{{ $patient->memo }}
        </div>
    @endif
    {{-- ▲ ここまで --}}
        
        <div style="margin-bottom: 15px; display: flex; gap: 15px; align-items: center;">
            <a href="{{ route('medicines.create', ['patient_id' => $patient->id]) }}" style="color: #1976D2; font-weight: bold;">[＋ お薬を追加]</a>
            <a href="{{ route('patients.show', $patient->id) }}" style="color: #9C27B0; border: 2px solid #9C27B0; padding: 5px 10px; border-radius: 8px; text-decoration: none; font-weight: bold; background: white;">📅 月間カレンダー</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 250px;">お薬名</th>
                    <th style="width: 150px;">分量</th>
                    <th>服用スケジュールと記録</th>
                    <th style="width: 100px;">管理</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($patient->medicines as $medicine)
                @php
                    $rowColorClass = $loop->odd ? 'bg-odd' : 'bg-even';
                @endphp
                <tr class="medicine-row {{ $rowColorClass }}">
                    <td class="{{ $rowColorClass }}">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($medicine->image_path)
                                <img src="{{ asset('storage/' . $medicine->image_path) }}" 
                                     class="clickable" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                     onclick="showBigDisplay('お薬の写真', '{{ $medicine->medicine_name }}', '{{ asset('storage/' . $medicine->image_path) }}', '', true)">
                            @endif
                            <strong class="medicine-name" style="font-size: 1.1em;">{{ $medicine->medicine_name }}</strong>
                        </div>
                    </td>
                    <td style="text-align: center;" class="{{ $rowColorClass }}">{{ $medicine->dosage }}</td>
                    <td class="{{ $rowColorClass }}">
                        @foreach ($medicine->schedules->sortBy('scheduled_time') as $schedule)
                            @php
                                $adherence = $schedule->adherences()->where('taken_date', now()->toDateString())->first();
                                $isTaken = (bool)$adherence;

                                // 予定時刻が過ぎていること、飲んだボタンを押していないこと、この2つの条件をチェックする必要がある。
                                // なぜなら、もう飲んだ薬まで「飲み忘れ」の警告が出続けるから
                                $scheduledTime = \Carbon\Carbon::parse($schedule->scheduled_time);
                                $isOverdue = !$isTaken && $scheduledTime->isBefore(now());
                            @endphp
                            
                            {{-- もし飲み忘れていたら 'alert-overdue' クラスを付与 --}}
                            <div class="schedule-box {{ $isOverdue ? 'alert-overdue' : '' }}">
                                <span class="scheduled-time {{ $isOverdue ? 'text-overdue' : '' }}">
                                    {{ $scheduledTime->format('H:i') }}
                                </span>
                                
                                <div class="action-area">
                                    @if($isTaken)
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="color: #2e7d32; font-weight: bold;" 
                                                  class="status-taken clickable"
                                                  onclick="showBigDisplay('{{ now()->format('n月j日') }} {{ $adherence->taken_time ? \Carbon\Carbon::parse($adherence->taken_time)->format('H:i') : $scheduledTime->format('H:i') }}', '{{ $medicine->medicine_name }}', '{{ $medicine->image_path ? asset('storage/' . $medicine->image_path) : '' }}', '{{ addslashes($adherence->note ?? '') }}')">
                                                ✅ 服用済み
                                            </span>
                                            <form action="{{ route('medicines.cancel') }}" method="POST" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                <button type="submit" class="btn-danger" style="padding: 2px 8px; font-size: 10px; border-radius: 4px;">取消</button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('medicines.take') }}" method="POST" style="margin:0; display: flex; align-items: center; gap: 8px;">
                                            @csrf
                                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                            <input type="text" name="note" placeholder="{{ $isOverdue ? '⚠️ 飲み忘れ！' : '体調メモ' }}" 
       style="font-size: 0.8em; padding: 6px; border-radius: 5px; border: 1px solid {{ $isOverdue ? '#f44336' : '#ddd' }}; width: 140px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
<button type="submit" class="btn" 
        style="background-color: {{ $isOverdue ? '#B71C1C' : '#2E7D32' }}; color: white; padding: 14px 15px; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); white-space: nowrap;">
    飲んだ！
</button>
                                            
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td style="text-align: center;" class="{{ $rowColorClass }}">
                        <a href="{{ route('medicines.edit', $medicine->id) }}" class="btn btn-edit" style="font-size: 16px; margin-bottom: 5px; width: 60px;">編集</a>
                        <form action="{{ route('medicines.destroy', $medicine->id) }}" method="POST" onsubmit="return confirm('このお薬を削除しますか？');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="font-size: 16px; width: 90px;">削除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endforeach

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
    /* モーダル表示スクリプト */
    function showBigDisplay(time, name, imagePath, note, isPhotoOnly = false) {
    document.getElementById('modal-time').innerText = isPhotoOnly ? time : time + ' の記録';
    document.getElementById('modal-name').innerText = name;
    
    // メモボックスの表示・非表示を切り替え
    const noteBox = document.getElementById('modal-note-box');
    if (isPhotoOnly) {
        noteBox.style.display = 'none'; // 写真だけの時は隠す
    } else {
        noteBox.style.display = 'block'; // それ以外は表示
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

    /* 通知設定スクリプト */
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationButton();
});

function updateNotificationButton() {
    const btn = document.getElementById('notify-btn');
    if (!btn) return;
    
    const notifyEnabled = localStorage.getItem('notifyEnabled') !== 'false';
    
    if (Notification.permission === 'denied') {
        btn.style.backgroundColor = '#f44336';
        btn.innerHTML = '❌ 通知がブロックされています';
    } else if (Notification.permission === 'granted' && notifyEnabled) {
    btn.style.backgroundColor = '#2E7D32';
    btn.innerHTML = '✅ 通知は有効です';
} else {
    btn.style.backgroundColor = '#616161';
    btn.innerHTML = '🔕 通知はOFFです';
}
}

function toggleNotifications() {
    if (Notification.permission === 'denied') {
        alert('通知の許可が必要です。ブラウザの設定から通知を許可してください。');
        return;
    }
    
    const notifyEnabled = localStorage.getItem('notifyEnabled') !== 'false';
    
    if (!notifyEnabled) {
        // OFFからONへ
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                localStorage.setItem('notifyEnabled', 'true');
                updateNotificationButton();
                new Notification("くすりサポート", { body: "通知が有効になりました！" });
            }
        });
    } else {
        // ONからOFFへ
        localStorage.setItem('notifyEnabled', 'false');
        updateNotificationButton();
    }
}

/* 服薬チェック・通知送信 */
setInterval(() => {
    if (Notification.permission !== 'granted') return;
    if (localStorage.getItem('notifyEnabled') === 'false') return;
    
    const now = new Date();
    const currentTime = now.getHours().toString().padStart(2, '0') + ":" + 
                        now.getMinutes().toString().padStart(2, '0');

    document.querySelectorAll('.schedule-box').forEach(box => {
        const timeVal = box.querySelector('.scheduled-time').innerText.trim();
        const isTaken = box.querySelector('.status-taken');
        
        if (timeVal === currentTime && !isTaken) {
            const medicineName = box.closest('tr').querySelector('.medicine-name').innerText;
            new Notification("お薬の時間です", {
                body: medicineName + " を飲む時間ですよ。",
            });
        }
    });
}, 60000);
  
</script>