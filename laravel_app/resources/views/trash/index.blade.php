<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ゴミ箱</title>
    <style>
        body { background-color: #fdfaf2; font-family: "Hiragino Sans", "Meiryo", sans-serif; margin: 20px; color: #333; }
        h1 { color: #4e342e; }
        h2 { color: #5d4037; border-bottom: 2px solid #d7ccc8; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        th { background-color: #efebe9; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #f0e6e6; }
        .btn-restore { background-color: #4CAF50; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; }
        .btn-delete { background-color: #f44336; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; }
        .empty { color: #999; font-style: italic; padding: 15px; }
        .back-btn { display: inline-block; margin-top: 20px; color: white; background-color: #5d4037; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: bold; }
        @if(session('success'))
        .alert { background-color: #c8e6c9; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; color: #2e7d32; }
        @endif
    </style>
</head>
<body>

<h1>🗑 ゴミ箱</h1>

@if(session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif

{{-- お薬のゴミ箱 --}}
<h2>💊 削除されたお薬</h2>
@if($trashedMedicines->isEmpty())
    <p class="empty">削除されたお薬はありません。</p>
@else
    <table>
        <tr>
            <th>患者名</th>
            <th>お薬名</th>
            <th>分量</th>
            <th>操作</th>
        </tr>
        @foreach($trashedMedicines as $group)
            @php $first = $group->first(); @endphp
            <tr>
                <td>
    {{ $first->patient->name ?? '不明' }}
    @if($first->patient && $first->patient->nickname)
        <br><small style="color: #999;">（{{ $first->patient->nickname }}）</small>
    @endif
</td>
                <td>{{ $first->medicine_name }}</td>
                <td>{{ $first->dosage }}</td>
                <td>
                    <form action="{{ route('trash.restore', $first->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-restore">元に戻す</button>
                    </form>
                    <form action="{{ route('trash.forceDelete', $first->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('完全に削除しますか？この操作は取り消せません。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">完全に消す</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@endif

{{-- 患者のゴミ箱 --}}
<h2>👤 削除された患者</h2>
@if($trashedPatients->isEmpty())
    <p class="empty">削除された患者はありません。</p>
@else
    <table>
        <tr>
            <th>患者名</th>
            <th>メモ</th>
            <th>操作</th>
        </tr>
        @foreach($trashedPatients as $patient)
            <tr>
                <td>
    {{ $patient->name }}
    @if($patient->nickname)
        <br><small style="color: #999;">（{{ $patient->nickname }}）</small>
    @endif
</td>
                <td>{{ $patient->memo ?? 'なし' }}</td>
                <td>
                    <form action="{{ route('trash.restore', $patient->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-restore">元に戻す</button>
                    </form>
                    <form action="{{ route('trash.forceDelete', $patient->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('完全に削除しますか？この操作は取り消せません。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">完全に消す</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@endif

<a href="/patients" class="back-btn">← 一覧に戻る</a>

</body>
</html>