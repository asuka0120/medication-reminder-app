{{-- 全体を中央に寄せるための外枠 --}}
<div style="max-width: 600px; margin: 50px auto; padding: 20px; font-family: 'Helvetica Neue', Arial, sans-serif;">
    
    <div style="margin-bottom: 20px;">
        <a href="/patients" style="color: #5D4037; text-decoration: none; font-weight: bold;">← 一覧に戻る</a>
    </div>

    {{-- 入力エリアをカード風にする --}}
    <div style="background-color: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #D7CCC8;">
        
        <h1 style="text-align: center; color: #4E342E; margin-bottom: 30px; font-size: 26px;">新規患者登録（ご家族用）</h1>

        <form action="{{ route('patients.store') }}" method="POST">
            @csrf

            {{-- お名前入力 --}}
            <div style="margin-bottom: 25px;">
                <label for="name" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">お名前（フルネーム）:</label>
                <input type="text" name="name" id="name" placeholder="例：山田 太郎"  
                    style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box; outline: none;">
                {{-- エラーメッセージ表示 --}}
    @error('name')
        <p style="color: #d32f2f; font-size: 14px; margin-top: 8px;">{{ $message }}</p>
    @enderror
                </div>

            {{-- 呼び名入力 --}}
            <div style="margin-bottom: 25px;">
                <label for="nickname" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">ご家族からの呼び名:</label>
                <input type="text" name="nickname" id="nickname" placeholder="例：おじいちゃん" 
                    style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box; outline: none;">
            </div>

            {{-- 備考入力 --}}
            <div style="margin-bottom: 35px;">
                <label for="memo" style="display: block; font-weight: bold; margin-bottom: 12px; font-size: 18px; color: #5D4037;">備考（注意点など）:</label>
                <textarea name="memo" id="memo" rows="4" placeholder="例：お水多めで。食後すぐ。" 
                    style="width: 100%; padding: 15px; font-size: 18px; border: 2px solid #D7CCC8; border-radius: 10px; box-sizing: border-box; outline: none; resize: vertical;"></textarea>
            </div>

            {{-- 登録ボタン --}}
            <button type="submit" 
                style="width: 100%; background-color: #4CAF50; color: white; padding: 20px; font-size: 20px; font-weight: bold; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                この内容で登録する
            </button>
        </form>
    </div>
</div>