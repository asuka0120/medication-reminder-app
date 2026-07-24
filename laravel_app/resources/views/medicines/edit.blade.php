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

@php
    // _dosage_field.blade.php などの共通パーツは新規登録・編集どちらからも使われるが、
    // 渡す変数の中身を画面ごとに変えることで、表示される内容も画面に合わせて変わる
    $dosageOptions = ['1回 1錠（食後）', '1回 2錠（食後）', '1回 1カプセル（食後）', '1回 1錠（寝る前）', '頓服（痛いときなど）'];
    $currentDosage = old('dosage_select', in_array($medicine->dosage, $dosageOptions) ? $medicine->dosage : 'other');
    $manualDosage = old('dosage_manual', (! in_array($medicine->dosage, $dosageOptions)) ? $medicine->dosage : '');

    $timeFieldName = 'timings';
    $fixedTimings = ['朝' => '08:00', '昼' => '12:00', '晩' => '18:00', '寝る前' => '21:00'];
    // 現在登録されている全時刻を取得（このお薬のスケジュールを直接参照するだけでよくなった）
    $checkedTimes = $medicine->schedules
                        ->pluck('scheduled_time')
                        ->map(fn ($t) => \Carbon\Carbon::parse($t)->format('H:i'))
                        ->toArray();
    // 固定選択肢以外の時間を「カスタム時間」として抽出
    $customTimes = array_diff($checkedTimes, array_values($fixedTimings));
@endphp

<div class="edit-container">
    <h1>💊 お薬情報の編集</h1>

    @include('partials.error_banner')

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
        @include('medicines._timing_fields')
        <p style="font-size: 0.85em; color: #666; margin-top: 8px;">
            ※チェックを外して更新すると、その時間のスケジュールは削除されます。
        </p>
        <br>

        {{-- 3. 分量・飲み方 --}}
        @include('medicines._dosage_field')
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

@include('medicines._scripts')
