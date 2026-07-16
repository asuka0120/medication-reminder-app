{{--
    患者（ご家族）の名前・呼び名・備考の入力欄（patients/create・patients/edit で共通）
    呼び出し側で $patient を渡すこと（新規登録時は null）
--}}
<div style="margin-bottom: 25px;">
    <label for="name" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">お名前（フルネーム）:</label>
    <input type="text" name="name" id="name" value="{{ old('name', $patient->name ?? '') }}" placeholder="例：山田 太郎"
        style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box; outline: none;">
    @error('name')
        <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
    @enderror
</div>

<div style="margin-bottom: 25px;">
    <label for="nickname" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">ご家族からの呼び名（任意）:</label>
    <input type="text" name="nickname" id="nickname" value="{{ old('nickname', $patient->nickname ?? '') }}" placeholder="例：おじいちゃん"
        style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box; outline: none;">
</div>

<div style="margin-bottom: 25px;">
    <label for="memo" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">備考（注意点など）:</label>
    <textarea name="memo" id="memo" rows="4" placeholder="例：お水多めで。食後すぐ。"
        style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box; outline: none; resize: vertical;">{{ old('memo', $patient->memo ?? '') }}</textarea>
</div>
