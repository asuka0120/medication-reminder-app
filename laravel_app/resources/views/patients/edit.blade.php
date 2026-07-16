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

        @include('patients._fields', ['patient' => $patient])

        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary">この内容で更新する</button>
            <a href="{{ route('patients.index') }}" class="btn btn-back">キャンセル</a>
        </div>
    </form>
</div>

</body>
</html>