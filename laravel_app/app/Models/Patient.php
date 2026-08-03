<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ゴミ箱機能：誤って削除すると、お薬の登録からやり直しになるため

class Patient extends Model
{
    use HasFactory;
    use SoftDeletes; // ゴミ箱機能：誤って削除すると、お薬の登録からやり直しになるため

    /**
     * 登録・編集を許可する列の一覧
     * ここに書いていない列はデータベースに保存されない（セキュリティ対策）
     *
     * user_id はあえて含めない：
     * この列はリクエスト側の値に依存せず、常にログインユーザーのIDを
     * サーバー側（User::patients()リレーション経由）で設定するべき値のため。
     * $fillable に含めてしまうと、将来 Patient::create($request->all()) のような
     * 書き方をされた場合に、送信された値で user_id を上書きされてしまう恐れがある。
     */
    protected $fillable = [
        'name',     // 患者さんの名前
        'nickname', // 家族が呼びやすい呼称（任意）
        'memo',     // ちょっとした注意書き（任意）
    ];

    /**
     * 「この患者は、一人のユーザーに属している」というルール
     * 例：患者「田中おじいちゃん」は、ユーザー「田中花子」が登録した
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 「一人の患者さんは、複数のお薬を持っている」というルール
     * 例：田中おじいちゃんは「ロキソニン」「カロナール」を飲んでいる
     */
    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }
}
