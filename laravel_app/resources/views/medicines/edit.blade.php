<h1>お薬情報の編集</h1>

<form action="{{ route('medicines.update', $medicine->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div>
        <label>お薬の名前:</label><br>
        <input type="text" name="medicine_name" value="{{ $medicine->medicine_name }}" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
    </div>
    <br>
    <div>
        <label>服用時刻:</label><br>
        <input type="time" name="scheduled_time" value="{{ \Carbon\Carbon::parse($medicine->scheduled_time)->format('H:i') }}" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
    </div>
    <br>
    <div>
        <label>分量・飲み方:</label><br>
        @php
            $options = ['1回 1錠（食後）', '1回 2錠（食後）', '1回 1カプセル（食後）', '1回 1錠（寝る前）', '頓服（痛いときなど）'];
            $isOther = !in_array($medicine->dosage, $options);
        @endphp

        <select id="dosage_select" name="dosage_select" onchange="toggleDosageInput('edit')" style="font-size: 1.2em; padding: 10px; width: 100%; border-radius: 8px; margin-bottom: 5px;">
            @foreach($options as $opt)
                <option value="{{ $opt }}" {{ $medicine->dosage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
            <option value="other" {{ $isOther ? 'selected' : '' }}>★その他（自由に書き込む）</option>
        </select>

        {{-- 初期状態で「その他」なら表示しておく --}}
        <div id="dosage_manual_group" style="display: {{ $isOther ? 'block' : 'none' }}; margin-top: 5px; padding: 10px; background-color: #f0f8ff; border-radius: 8px; border: 1px dashed #2196F3;">
            <label style="font-size: 0.8em; color: #2196F3;">具体的な飲み方を入力：</label><br>
            <input type="text" id="dosage_manual" name="dosage_manual" value="{{ $isOther ? $medicine->dosage : '' }}" placeholder="例：半分に割って飲む" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        </div>
    </div>
    <br>
    <div>
        <label>現在の写真:</label><br>
        @if($medicine->image_path)
            <img src="{{ asset('storage/' . $medicine->image_path) }}" style="width: 120px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px;"><br>
        @endif
        <label>新しい写真に変更する場合（任意）:</label><br>
        <input type="file" name="image" accept="image/*">
    </div>
    <br>
    <button type="submit" style="background-color: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; font-weight: bold; width: 100%;">
        更新する
    </button>
</form>

<br>
<a href="/patients">キャンセルして戻る</a>

<script>
function toggleDosageInput(mode) {
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