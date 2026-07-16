{{-- 共通のエラーメッセージ表示欄（セッションに 'error_message' がある場合のみ表示） --}}
@if(session('error_message'))
    <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; border: 1px solid #ef9a9a; margin-bottom: 20px; font-weight: bold;">
        ⚠️ {{ session('error_message') }}
    </div>
@endif
