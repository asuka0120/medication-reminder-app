{{-- 全体を中央に寄せるための外枠 --}}
<div style="max-width: 600px; margin: 50px auto; padding: 20px; font-family: 'Helvetica Neue', Arial, sans-serif;">
    
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('patients.index') }}" style="color: #5D4037; text-decoration: none; font-weight: bold;">← 一覧に戻る</a>
        @include('partials.logout_link')
    </div>

    {{-- 入力エリアをカード風にする --}}
    <div style="background-color: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #D7CCC8;">
        
        <h1 style="text-align: center; color: #4E342E; margin-bottom: 30px; font-size: 26px;">新規患者登録（ご家族用）</h1>

        <form action="{{ route('patients.store') }}" method="POST">
            @csrf

            @include('patients._fields', ['patient' => null])

            {{-- 登録ボタン --}}
            <button type="submit" 
                style="width: 100%; background-color: #4CAF50; color: white; padding: 20px; font-size: 20px; font-weight: bold; border: none; border-radius: 12px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                この内容で登録する
            </button>
        </form>
    </div>
</div>