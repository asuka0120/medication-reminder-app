{{--
    服用時刻の選択欄（medicines/create・medicines/edit で共通）
    呼び出し側で以下の変数を渡すこと：
      $timeFieldName : input の name 属性（新規登録は 'times'、更新は 'timings'）
      $fixedTimings  : 固定の選択肢（例: ['朝' => '08:00', ...]）
      $checkedTimes  : すでにチェックが入っているべき時刻の配列（新規登録時は空配列）
      $customTimes   : 固定選択肢に含まれない「自由入力」の時刻の配列（新規登録時は空配列）
--}}
<div class="form-group">
    <label>飲むタイミング（複数選ぶと一括で登録・更新されます）:</label>
    <div class="timing-grid">
        @foreach($fixedTimings as $label => $time)
            <label class="timing-item">
                <input type="checkbox" name="{{ $timeFieldName }}[]" value="{{ $time }}"
                    {{ in_array($time, $checkedTimes) ? 'checked' : '' }}>
                <span class="timing-label">{{ $label }} ({{ $time }})</span>
            </label>
        @endforeach
    </div>

    {{-- 自由な時間入力エリア --}}
    <div id="custom-times-container" style="margin-top: 10px;">
        @foreach($customTimes as $customTime)
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
                <input type="time" name="{{ $timeFieldName }}[]" value="{{ $customTime }}" class="input-field" style="width: 140px; padding: 5px;">
                <span style="font-size: 0.8em; color: #666;">（自由指定）</span>
                <button type="button" onclick="this.parentElement.remove()" style="color: #f44336; border: none; background: none; cursor: pointer; font-weight: bold; font-size: 1.2em; margin-left: auto;">✕</button>
            </div>
        @endforeach
    </div>

    @error($timeFieldName)
        <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
    @enderror
    @error("{$timeFieldName}.*")
        <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
    @enderror

    <button type="button" onclick="addCustomTime()" style="margin-top: 10px; background-color: #efebe9; border: 1px solid #d7ccc8; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-size: 0.9em; color: #5d4037; font-weight: bold; width: 100%;">
        ＋ 食間など、他の時間を追加する
    </button>
    <p style="font-size: 0.8em; color: #666; margin-top: 5px;">※チェックした時間ごとに通知が届くようになります。</p>
</div>