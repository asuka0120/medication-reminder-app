<h1>新規患者登録（ご家族用）</h1>

<form action="/patients" method="POST">
    @csrf
    <div>
        <label>お名前（フルネーム）:</label><br>
        <input type="text" name="name" required placeholder="例：山田 太郎">
    </div>
    <br>
    <div>
        <label>ご家族からの呼び名:</label><br>
        <input type="text" name="nickname" placeholder="例：おじいちゃん">
    </div>
    <br>
    <div>
        <label>備考（注意点など）:</label><br>
        <textarea name="memo" placeholder="例：お水多めで。食後すぐ。"></textarea>
    </div>
    <br>
    <button type="submit">この内容で登録する</button>
</form>

<br>
<a href="/patients">一覧に戻る</a>