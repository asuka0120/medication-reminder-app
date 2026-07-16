{{--
    分量・飲み方の選択欄（medicines/create・medicines/edit で共通）
    呼び出し側で以下の変数を渡すこと：
      $dosageOptions  : 選択肢の配列（例: ['1回 1錠（食後）', ...]）
      $currentDosage  : 現在選ばれている値（'other' の場合は手入力欄を開く）
      $manualDosage   : 手入力欄の初期値
--}}
<div class="form-group">
    <label>分量・飲み方:</label>
    <select id="dosage_select" name="dosage_select" onchange="toggleDosageInput()" class="input-field" style="background-color: white;">
        @foreach($dosageOptions as $option)
            <option value="{{ $option }}" {{ $currentDosage === $option ? 'selected' : '' }}>{{ $option }}</option>
        @endforeach
        <option value="other" {{ $currentDosage === 'other' ? 'selected' : '' }}>★その他（自由に書き込む）</option>
    </select>

    <div id="dosage_manual_group" style="display: {{ $currentDosage === 'other' ? 'block' : 'none' }}; margin-top: 10px; padding: 15px; background-color: #f0f8ff; border-radius: 8px; border: 1px dashed #2196F3;">
        <label style="font-size: 0.9em; color: #2196F3; font-weight: bold;">具体的な飲み方を入力：</label>
        <input type="text" id="dosage_manual" name="dosage_manual" value="{{ $manualDosage }}" placeholder="例：半分に割って飲む" class="input-field">
    </div>
</div>
