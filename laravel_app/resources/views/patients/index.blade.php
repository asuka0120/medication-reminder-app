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
        padding: 8px 15px;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        transition: 0.3s;
    }
    .btn-danger { background-color: #e53935; color: white; }
    .btn-edit { background-color: #1e88e5; color: white; }

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

</style>

<h1>ご家族のための服薬管理（くすりサポート）</h1>

<div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
    <a href="/patients/create" style="text-decoration: none; font-weight: bold; color: #1976D2;">＋ 新しく家族を登録する</a>
    <a href="{{ route('medicines.trash') }}" style="color: #607D8B; text-decoration: none;">[ 🗑 ゴミ箱を見る ]</a>
    <button id="notify-btn" onclick="enableNotifications()" class="btn" style="background-color: #ff9800; color: white; min-width: 180px;">
        🔔 通知設定を確認中...
    </button>
</div>

<hr style="border: 0; border-top: 1px solid #d7ccc8; margin: 20px 0;">

@foreach ($patients as $patient)
    <div class="patient-card">
        <h3 style="margin-top: 0; color: #4e342e;">{{ $patient->name }}（{{ $patient->nickname }}）さん</h3>
        
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
            @foreach ($patient->medicines->groupBy('medicine_name') as $name => $group)
                @php 
                    $first = $group->first(); 
                    // 1つおきに背景色を変えるクラスを判定
                    $rowColorClass = $loop->odd ? 'bg-odd' : 'bg-even';
                @endphp
                {{-- ここで背景色と太い境界線を適用 --}}
                <tr class="medicine-row {{ $rowColorClass }}">
                    <td class="{{ $rowColorClass }}">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($first->image_path)
                                <img src="{{ asset('storage/' . $first->image_path) }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            @endif
                            <strong class="medicine-name" style="font-size: 1.1em;">{{ $name }}</strong>
                        </div>
                    </td>
                    <td style="text-align: center;" class="{{ $rowColorClass }}">{{ $first->dosage }}</td>
                    <td class="{{ $rowColorClass }}">
                        @foreach ($group->sortBy('scheduled_time') as $medicine)
                            @php
                                $isTaken = $medicine->adherences()->where('taken_date', now()->toDateString())->exists();
                            @endphp
                            <div class="schedule-box">
                                <span class="scheduled-time">
                                    {{ \Carbon\Carbon::parse($medicine->scheduled_time)->format('H:i') }}
                                </span>
                                
                                <div class="action-area">
                                    @if($isTaken)
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="color: #2e7d32; font-weight: bold;" class="status-taken">✅ 服用済み</span>
                                            <form action="{{ route('adherences.cancel') }}" method="POST" style="display:inline;">
                                                @csrf
                                                <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                                                <button type="submit" class="btn-danger" style="padding: 2px 8px; font-size: 10px; border-radius: 4px;">取消</button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="{{ route('adherences.store') }}" method="POST" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="medicine_id" value="{{ $medicine->id }}">
                                            <button type="submit" class="btn" style="background-color: #4CAF50; color: white; padding: 5px 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">飲んだ！</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td style="text-align: center;" class="{{ $rowColorClass }}">
                        <a href="{{ route('medicines.edit', $first->id) }}" class="btn btn-edit" style="font-size: 12px; margin-bottom: 5px; width: 60px;">編集</a>
                        <form action="{{ route('medicines.destroy', $first->id) }}" method="POST" onsubmit="return confirm('同じ名前のスケジュールを全て削除しますか？');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="font-size: 12px; width: 60px;">削除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endforeach

<script>
    /* （通知スクリプト部分は変更なし） */
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationButton();
    });

    function updateNotificationButton() {
        const btn = document.getElementById('notify-btn');
        if (!btn) return;
        if (Notification.permission === 'granted') {
            btn.style.backgroundColor = '#4CAF50';
            btn.innerHTML = '✅ 通知は有効です';
        } else if (Notification.permission === 'denied') {
            btn.style.backgroundColor = '#f44336';
            btn.innerHTML = '❌ 通知がブロックされています';
        } else {
            btn.style.backgroundColor = '#ff9800';
            btn.innerHTML = '🔔 通知設定をオンにする';
        }
    }

    function enableNotifications() {
        Notification.requestPermission().then(permission => {
            updateNotificationButton();
            if (permission === 'granted') {
                new Notification("くすりサポート", { body: "通知が有効になりました！" });
            }
        });
    }

    setInterval(() => {
        if (Notification.permission !== 'granted') return;
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