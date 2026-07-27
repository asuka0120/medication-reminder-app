<style>
    .form-container { max-width: 600px; margin: auto; padding: 20px; font-family: "Hiragino Sans", "Meiryo", sans-serif; }
    .form-group { margin-bottom: 20px; }
    label { font-weight: bold; display: block; margin-bottom: 8px; color: #5d4037; }
    .input-field { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; box-sizing: border-box; }

    /* 一括登録用のチェックボックスエリア */
    .timing-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        background-color: #fdf5e6;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #d7ccc8;
    }
    .timing-item {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px;
        background: white;
        border-radius: 5px;
        border: 1px solid #eee;
    }
    .timing-item input { width: 20px; height: 20px; cursor: pointer; }
    .timing-label { font-size: 1.1em; font-weight: bold; }

    .btn-submit {
        background-color: #4CAF50; color: white; padding: 15px 30px;
        border: none; border-radius: 10px; cursor: pointer;
        font-size: 1.2em; font-weight: bold; width: 100%; transition: 0.3s;
    }
    .btn-submit:hover { background-color: #45a049; }
</style>

@php
    // _dosage_field.blade.php などの共通パーツは新規登録・編集どちらからも使われるが、
    // 渡す変数の中身を画面ごとに変えることで、表示される内容も画面に合わせて変わる
    $dosageOptions = ['1回 1錠（食後）', '1回 2錠（食後）', '1回 1カプセル（食後）', '1回 1錠（寝る前）', '頓服（痛いときなど）'];
    $currentDosage = old('dosage_select', $dosageOptions[0]);
    $manualDosage = old('dosage_manual', '');

    $timeFieldName = 'times';
    $fixedTimings = ['朝' => '08:00', '昼' => '12:00', '晩' => '18:00', '寝る前' => '21:00'];
    // 入力エラーで差し戻された場合、前回選択していた時刻を復元する
    $checkedTimes = old('times', []);
    $customTimes = array_diff($checkedTimes, array_values($fixedTimings));
@endphp

<div class="form-container">
    <h1>💊 {{ $patient->name }} さんのお薬登録</h1>

    @include('partials.error_banner')

    <form action="{{ route('medicines.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return checkTimes()">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
        @error('patient_id')
            <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
        @enderror

        {{-- 1. お薬名 --}}
        <div class="form-group">
            <label>お薬の名前:</label>
            <input type="text" name="medicine_name" value="{{ old('medicine_name') }}" placeholder="例：アムロジピン" class="input-field">
            @error('medicine_name')
                <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- 2. 服用タイミング（一括登録の肝） --}}
        @include('medicines._timing_fields')

        {{-- 3. 分量・飲み方 --}}
        @include('medicines._dosage_field')

        {{-- 4. 写真 --}}
        <div class="form-group">
            <label>お薬の写真（任意）:</label>
            <input type="file" name="image" accept="image/*" class="input-field">
            @error('image')
                <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            まとめて登録する
        </button>
    </form>

    <br>
    <a href="{{ route('patients.index') }}" style="display: block; text-align: center; color: #607D8B; text-decoration: none;">← 一覧に戻る</a>
</div>

@include('medicines._scripts')