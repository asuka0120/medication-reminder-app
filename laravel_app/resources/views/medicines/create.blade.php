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

<div class="form-container">
    <h1>💊 {{ $patient->name }} さんのお薬登録</h1>

    <form action="{{ route('medicines.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $patient->id }}">

        {{-- 1. お薬名 --}}
        <div class="form-group">
            <label>お薬の名前:</label>
            <input type="text" name="medicine_name" required placeholder="例：アムロジピン" class="input-field">
        </div>

        {{-- 2. 服用タイミング（一括登録の肝） --}}
        <div class="form-group">
            <label>飲むタイミング（複数選ぶと一括登録されます）:</label>
            <div class="timing-grid">
                <label class="timing-item">
                    <input type="checkbox" name="times[]" value="08:00">
                    <span class="timing-label">朝 (08:00)</span>
                </label>
                <label class="timing-item">
                    <input type="checkbox" name="times[]" value="12:00">
                    <span class="timing-label">昼 (12:00)</span>
                </label>
                <label class="timing-item">
                    <input type="checkbox" name="times[]" value="18:00">
                    <span class="timing-label">晩 (18:00)</span>
                </label>
                <label class="timing-item">
                    <input type="checkbox" name="times[]" value="21:00">
                    <span class="timing-label">寝る前 (21:00)</span>
                </label>
            </div>
            <p style="font-size: 0.8em; color: #666; margin-top: 5px;">※チェックした時間ごとに通知が届くようになります。</p>
        </div>

        {{-- 3. 分量・飲み方 --}}
        <div class="form-group">
            <label>分量・飲み方:</label>
            <select id="dosage_select" name="dosage_select" onchange="toggleDosageInput('create')" class="input-field" style="background-color: white;">
                <option value="1回 1錠（食後）">1回 1錠（食後）</option>
                <option value="1回 2錠（食後）">1回 2錠（食後）</option>
                <option value="1回 1カプセル（食後）">1回 1カプセル（食後）</option>
                <option value="1回 1錠（寝る前）">1回 1錠（寝る前）</option>
                <option value="頓服（痛いときなど）">頓服（痛いときなど）</option>
                <option value="other">★その他（自由に書き込む）</option>
            </select>

            <div id="dosage_manual_group" style="display: none; margin-top: 10px; padding: 15px; background-color: #f0f8ff; border-radius: 8px; border: 1px dashed #2196F3;">
                <label style="font-size: 0.8em; color: #2196F3;">具体的な飲み方を入力：</label>
                <input type="text" id="dosage_manual" name="dosage_manual" placeholder="例：半分に割って飲む" class="input-field">
            </div>
        </div>

        {{-- 4. 写真 --}}
        <div class="form-group">
            <label>お薬の写真（任意）:</label>
            <input type="file" name="image" accept="image/*" class="input-field">
        </div>

        <button type="submit" class="btn-submit">
            まとめて登録する
        </button>
    </form>

    <br>
    <a href="/patients" style="display: block; text-align: center; color: #607D8B; text-decoration: none;">← 一覧に戻る</a>
</div>

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
        if(mode === 'create') manualInput.value = ''; 
    }
}
</script>