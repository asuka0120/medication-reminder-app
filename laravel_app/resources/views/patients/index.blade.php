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
        background-color: #ffffff;
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

    /* 【修正ポイント】スケジュールを左寄せでコンパクトに並べるスタイル */
    .schedule-box {
        display: flex;
        align-items: center;
        justify-content: space-between; /* 時刻を左、ボタンを右に配置 */
        gap: 40px;                   /* 時刻とボタンの間隔 */
        background-color: #fcfcfc;
        border: 1px solid #eee;
        padding: 10px 15px;          /* 上下左右の余白も少し広げるとより綺麗です */
        border-radius: 8px;
        margin: 8px auto;               /* 「auto」で項目の中央に配置 */
        width: 350px;                   /* ボックスの幅を広げて固定（5cm相当の余白） */
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .scheduled-time {
        min-width: 60px;             /* 時刻の幅を揃えて見やすくする */
    }
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
                @php $first = $group->first(); @endphp
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if($first->image_path)
                                <img src="{{ asset('storage/' . $first->image_path) }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            @endif
                            <strong class="medicine-name" style="font-size: 1.1em;">{{ $name }}</strong>
                        </div>
                    </td>
                    <td style="text-align: center;">{{ $first->dosage }}</td>
                    <td>
                        @foreach ($group->sortBy('scheduled_time') as $medicine)
                            @php
                                $isTaken = $medicine->adherences()->where('taken_date', now()->toDateString())->exists();
                            @endphp
                            <div class="schedule-box">
                                <span class="scheduled-time" style="font-weight: bold; font-size: 1.1em;">
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
                    <td style="text-align: center;">
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
    /* （通知スクリプト部分は変更なしのため継続してご利用いただけます） */
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