<h1>ゴミ箱（削除済みのお薬一覧）</h1>

<div style="margin-bottom: 20px;">
    <a href="/patients">← お薬一覧に戻る</a>
</div>

@if($trashedMedicines->isEmpty())
    <p>ゴミ箱は空です。</p>
@else
    <table border="1" style="width: 100%; border-collapse: collapse; text-align: center;">
        <tr style="background-color: #e0e0e0;">
            <th style="padding: 8px;">患者名</th>
            <th style="padding: 8px;">お薬名</th>
            <th style="padding: 8px;">分量</th>
            <th style="padding: 8px;">操作</th>
        </tr>
        @foreach ($trashedMedicines as $medicine)
            <tr>
                <td style="padding: 8px;">{{ $medicine->patient->name }}</td>
                <td style="padding: 8px;">{{ $medicine->medicine_name }}</td>
                <td style="padding: 8px;">{{ $medicine->dosage }}</td>
                <td style="padding: 8px;">
                    <form action="{{ route('medicines.restore', $medicine->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="background-color: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">元に戻す</button>
                    </form>

                    <form action="{{ route('medicines.forceDelete', $medicine->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('本当に完全に削除しますか？この操作は取り消せません。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background-color: #f44336; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">完全に消す</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </table>
@endif