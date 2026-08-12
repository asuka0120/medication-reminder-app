{{--
    ログアウトへの導線（共通パーツ）
    このアプリの主要画面（お薬一覧・カレンダー・週間レポート・ゴミ箱など）は
    Laravel Breezeの共通レイアウト（ヘッダーのドロップダウン）を使っていないため、
    各画面のヘッダー部分にこのパーシャルを読み込んでログアウトへの導線を確保する。
--}}
<form method="POST" action="{{ route('logout') }}" style="display: inline;">
    @csrf
    <button type="submit" style="background: none; border: none; color: #607D8B; text-decoration: none; cursor: pointer; font-size: 1em; font-weight: normal; padding: 0;">
        🚪 ログアウト
    </button>
</form>