<style>
    body {
        background-color: #fdfaf2;
        color: #333333;
        font-family: "Hiragino Sans", "Meiryo", sans-serif;
        margin: 20px;
    }
    .edit-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        border: 2px solid #d7ccc8;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    /* チェックボックスをカード状に見せるスタイル（登録画面と統一） */
    .timing-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 10px;
        background: #fff8e1;
        padding: 20px;
        border-radius: 10px;
    }
    .timing-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ffe082;
        cursor: pointer;
    }
    .timing-item input {
        transform: scale(1.5);
    }
</style>

<div class="edit-container">
    <h1>💊 お薬情報の編集</h1>

    <form action="{{ route('medicines.update', $medicine->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- お薬の名前 --}}
        <div>
            <label style="font-weight: bold;">お薬の名前:</label><br>
            <input type="text" name="medicine_name" value="{{ $medicine->medicine_name }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; font-size: 1.1em; margin-top: 5px;">
        </div>
        <br>

        {{-- 服用時刻（一括更新用チェックボックス） --}}
        <div>
            <label style="font-weight: bold;">飲むタイミング（複数選ぶと一括でスケジュールを更新します） :</label>
            <div class="timing-grid">
                @php
                    // 同じ名前のお薬がどの時間に登録されているかをチェック状態に反映
                    $registeredTimes = \App\Models\Medicine::where('patient_id', $medicine->patient_id)
                                        ->where('medicine_name', $medicine->medicine_name)
                                        ->whereNull('deleted_at')
                                        ->pluck('scheduled_time')
                                        ->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i'))
                                        ->toArray();
                    
                    $timings = [
                        '朝' => '08:00',
                        '昼' => '12:00',
                        '晩' => '18:00',
                        '寝る前' => '21:00'
                    ];
                @endphp

                @foreach($timings as $label => $time)
                    <label class="timing-item">
                        <input type="checkbox" name="timings[]" value="{{ $time }}" 
                            {{ in_array($time, $registeredTimes) ? 'checked' : '' }}>
                        <span>{{ $label }} ({{ $time }})</span>
                    </label>
                @endforeach
            </div>
            <p style="font-size: 0.85em; color: #666; margin-top: 8px;">
                ※チェックを外して更新すると、その時間のスケジュールは削除されます。
            </p>
        </div>
        <br>

        {{-- 分量・飲み方 --}}
        <div>
            <label style="font-weight: bold;">分量・飲み方:</label><br>
            @php
                $options = ['1回 1錠（食後）', '1回 2錠（食後）', '1回 1カプセル（食後）', '1回 1錠（寝る前）', '頓服（痛いときなど）'];
                $isOther = !in_array($medicine->dosage, $options);
            @endphp

            <select id="dosage_select" name="dosage_select" onchange="toggleDosageInput()" style="font-size: 1.1em; padding: 12px; width: 100%; border-radius: 8px; margin-top: 5px; border: 1px solid #ccc;">
                @foreach($options as $opt)
                    <option value="{{ $opt }}" {{ $medicine->dosage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
                <option value="other" {{ $isOther ? 'selected' : '' }}>★その他（自由に書き込む）</option>
            </select>

            <div id="dosage_manual_group" style="display: {{ $isOther ? 'block' : 'none' }}; margin-top: 10px; padding: 15px; background-color: #f0f8ff; border-radius: 8px; border: 1px dashed #2196F3;">
                <label style="font-size: 0.9em; color: #2196F3; font-weight: bold;">具体的な飲み方を入力：</label><br>
                <input type="text" id="dosage_manual" name="dosage_manual" value="{{ $isOther ? $medicine->dosage : '' }}" placeholder="例：半分に割って飲む" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-top: 5px;">
            </div>
        </div>
        <br>

        {{-- 写真の管理 --}}
        <div>
            <label style="font-weight: bold;">現在の写真:</label><br>
            @if($medicine->image_path)
                <div style="margin: 10px 0;">
                    <img src="{{ asset('storage/' . $medicine->image_path) }}" style="width: 150px; border-radius: 10px; border: 2px solid #ddd;">
                </div>
            @else
                <p style="color: #999; font-size: 0.9em;">（写真は登録されていません）</p>
            @endif
            <label style="font-weight: bold;">写真を変更または追加（任意）:</label><br>
            <input type="file" name="image" accept="image/*" style="margin-top: 5px;">
        </div>
        <br><br>

        <button type="submit" style="background-color: #4CAF50; color: white; padding: 15px; border: none; border-radius: 10px; cursor: pointer; font-size: 1.2em; font-weight: bold; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            内容を更新する
        </button>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/patients" style="color: #666; text-decoration: none;">← キャンセルして戻る</a>
    </div>
</div>

<script>
function toggleDosageInput() {
    const select = document.getElementById('dosage_select');
    const manualGroup = document.getElementById('dosage_manual_group');
    const manualInput = document.getElementById('dosage_manual');

    if (select.value === 'other') {
        manualGroup.style.display = 'block';
        manualInput.focus();
    } else {
        manualGroup.style.display = 'none';
    }
}
</script>