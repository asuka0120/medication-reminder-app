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
    .input-field { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; }
    .btn-submit {
        background-color: #4CAF50; color: white; padding: 15px; border: none; border-radius: 10px; cursor: pointer;
        font-size: 1.2em; font-weight: bold; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 20px;
    }
</style>

<div class="edit-container">
    <h1>💊 お薬情報の編集</h1>

    {{-- ★警告メッセージの表示エリア --}}
    @if(session('error_message'))
        <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; border: 1px solid #ef9a9a; margin-bottom: 20px; font-weight: bold;">
            ⚠️ {{ session('error_message') }}
        </div>
    @endif

    <form action="{{ route('medicines.update', $medicine->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return checkTimes()">
        @csrf
        @method('PUT')

        {{-- 1. お薬の名前 --}}
        <div>
            <label style="font-weight: bold;">お薬の名前:</label><br>
            <input type="text" name="medicine_name" value="{{ old('medicine_name', $medicine->medicine_name) }}" required class="input-field" style="margin-top: 5px;">
        </div>
        <br>

        {{-- 2. 服用時刻（一括更新用） --}}
        <div>
            <label style="font-weight: bold;">飲むタイミング（複数選ぶと一括でスケジュールを更新します）:</label>
            <div class="timing-grid">
                @php
                    // 現在登録されている全時刻を取得（このお薬のスケジュールを直接参照するだけでよくなった）
                    $allRegisteredTimes = $medicine->schedules
                                        ->pluck('scheduled_time')
                                        ->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i'))
                                        ->toArray();
                    
                    // 固定の選択肢
                    $fixedTimings = [
                        '朝' => '08:00',
                        '昼' => '12:00',
                        '晩' => '18:00',
                        '寝る前' => '21:00'
                    ];

                    // 固定選択肢以外の時間を「カスタム時間」として抽出
                    $customTimes = array_diff($allRegisteredTimes, array_values($fixedTimings));
                @endphp

                @foreach($fixedTimings as $label => $time)
                    <label class="timing-item">
                        <input type="checkbox" name="timings[]" value="{{ $time }}" 
                            {{ in_array($time, $allRegisteredTimes) ? 'checked' : '' }}>
                        <span>{{ $label }} ({{ $time }})</span>
                    </label>
                @endforeach
            </div>

            {{-- 自由な時間入力エリア --}}
            <div id="custom-times-container" style="margin-top: 10px;">
                @foreach($customTimes as $cTime)
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
                        <input type="time" name="timings[]" value="{{ $cTime }}" class="input-field" style="width: 140px; padding: 5px;">
                        <span style="font-size: 0.8em; color: #666;">（自由指定）</span>
                        <button type="button" onclick="this.parentElement.remove()" style="color: #f44336; border: none; background: none; cursor: pointer; font-weight: bold; font-size: 1.2em; margin-left: auto;">✕</button>
                    </div>
                @endforeach
            </div>

            <button type="button" onclick="addCustomTime()" style="margin-top: 10px; background-color: #efebe9; border: 1px solid #d7ccc8; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-size: 0.9em; color: #5d4037; font-weight: bold; width: 100%;">
                ＋ 食間など、他の時間を追加する
            </button>
            <p style="font-size: 0.85em; color: #666; margin-top: 8px;">
                ※チェックを外して更新すると、その時間のスケジュールは削除されます。
            </p>
        </div>
        <br>

        {{-- 3. 分量・飲み方 --}}
        <div>
            <label style="font-weight: bold;">分量・飲み方:</label><br>
            @php
                $options = ['1回 1錠（食後）', '1回 2錠（食後）', '1回 1カプセル（食後）', '1回 1錠（寝る前）', '頓服（痛いときなど）'];
                $currentDosage = old('dosage_select', in_array($medicine->dosage, $options) ? $medicine->dosage : 'other');
                $manualDosage = old('dosage_manual', (!in_array($medicine->dosage, $options)) ? $medicine->dosage : '');
            @endphp

            <select id="dosage_select" name="dosage_select" onchange="toggleDosageInput()" class="input-field" style="margin-top: 5px;">
                @foreach($options as $opt)
                    <option value="{{ $opt }}" {{ $currentDosage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
                <option value="other" {{ $currentDosage == 'other' ? 'selected' : '' }}>★その他（自由に書き込む）</option>
            </select>

            <div id="dosage_manual_group" style="display: {{ $currentDosage == 'other' ? 'block' : 'none' }}; margin-top: 10px; padding: 15px; background-color: #f0f8ff; border-radius: 8px; border: 1px dashed #2196F3;">
                <label style="font-size: 0.9em; color: #2196F3; font-weight: bold;">具体的な飲み方を入力：</label><br>
                <input type="text" id="dosage_manual" name="dosage_manual" value="{{ $manualDosage }}" placeholder="例：半分に割って飲む" class="input-field" style="margin-top: 5px;">
            </div>
        </div>
        <br>

        {{-- 4. 写真の管理 --}}
        <div>
            <label style="font-weight: bold;">現在の写真:</label><br>
            @if($medicine->image_path)
                <div style="margin: 10px 0;">
                    <img src="{{ asset('storage/' . $medicine->image_path) }}" style="width: 150px; border-radius: 10px; border: 2px solid #ddd;">
                </div>
            @else
                <p style="color: #999; font-size: 0.9em;">（写真は登録されていません）</p>
            @endif
            <label style="font-weight: bold;">写真を変更（任意）:</label><br>
            <input type="file" name="image" accept="image/*" style="margin-top: 5px;">
        </div>

        <button type="submit" class="btn-submit">内容を更新する</button>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('patients.index') }}" style="color: #666; text-decoration: none;">← キャンセルして戻る</a>
    </div>
</div>

<script>
// 自由な時間を追加する関数（createと同様）
function addCustomTime() {
    const container = document.getElementById('custom-times-container');
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.alignItems = 'center';
    wrapper.style.gap = '10px';
    wrapper.style.marginBottom = '8px';
    wrapper.style.padding = '10px';
    wrapper.style.background = '#fff';
    wrapper.style.border = '1px solid #ddd';
    wrapper.style.borderRadius = '8px';

    wrapper.innerHTML = `
        <input type="time" name="timings[]" class="input-field" style="width: 140px; padding: 5px;">
        <span style="font-size: 0.8em; color: #666;">（自由指定）</span>
        <button type="button" onclick="this.parentElement.remove()" style="color: #f44336; border: none; background: none; cursor: pointer; font-weight: bold; font-size: 1.2em; margin-left: auto;">✕</button>
    `;
    container.appendChild(wrapper);
}

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

// 送信前のバリデーション（createと同様、ただし名前を timings[] に対応）
function checkTimes() {
    const checkboxes = document.querySelectorAll('input[name="timings[]"]:checked');
    const customInputs = document.querySelectorAll('input[type="time"][name="timings[]"]');
    
    let hasTime = checkboxes.length > 0;

    customInputs.forEach(input => {
        if (input.value !== "") {
            hasTime = true;
        }
    });

    if (!hasTime) {
        alert("服用時間を設定してください。\n最低でも1つの時間を選択するか、追加する必要があります。");
        return false; 
    }
    return true; 
}
</script>