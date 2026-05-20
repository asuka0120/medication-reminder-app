<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家族情報の編集</title>
    <style>
        body {
            background-color: #fdfaf2;
            color: #333333;
            font-family: "Hiragino Sans", "Meiryo", sans-serif;
            margin: 20px;
        }
        .form-card {
            background-color: #ffffff;
            border: 2px solid #d7ccc8;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .btn {
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
        }
        .btn-primary { background-color: #4CAF50; color: white; }
        .btn-back { background-color: #9e9e9e; color: white; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>

<h1 style="color: #4e342e;">家族情報の編集</h1>

<div class="form-card">
    <form action="{{ route('patients.update', $patient->id) }}" method="POST">
        @csrf
        @method('PATCH')

        {{-- お名前入力 --}}
        <div style="margin-bottom: 25px;">
            <label for="name" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">お名前（フルネーム）:</label>
            <input type="text" name="name" id="name" value="{{ old('name', $patient->name) }}"
                style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box;">
            {{-- エラーメッセージ表示 --}}
            @error('name')
                <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- ニックネーム入力 --}}
        <div style="margin-bottom: 25px;">
            <label for="nickname" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">ご家族からの呼び名（任意）:</label>
            <input type="text" name="nickname" id="nickname" value="{{ old('nickname', $patient->nickname) }}" placeholder="例：おじいちゃん"
                style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box;">
        </div>

        {{-- メモ入力 --}}
        <div style="margin-bottom: 25px;">
            <label for="memo" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">備考（注意点など）:</label>
            <textarea name="memo" id="memo" placeholder="例：お水多めで。食後すぐ。" rows="4"
                style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box;">{{ old('memo', $patient->memo) }}</textarea>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary">この内容で更新する</button>
            <a href="/patients" class="btn btn-back">キャンセル</a>
        </div>
    </form>
</div>

</body>
</html>