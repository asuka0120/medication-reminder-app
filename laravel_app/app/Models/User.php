<?php

namespace App\Models;

// Breeze導入時の初期状態のまま。メール認証機能はまだ手をつけていない
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * 登録・編集を許可する列の一覧
     * ここに書いていない列はデータベースに保存されない（セキュリティ対策）
     */
    protected $fillable = [
        'name',     // ユーザーの名前
        'email',    // メールアドレス（ログインに使用）
        'password', // パスワード（自動的にハッシュ化される）
    ];

    /**
     * レスポンスに含めない列の一覧
     * パスワードなどの機密情報を外部に漏らさないようにする
     */
    protected $hidden = [
        'password',      // パスワードは絶対に外部に見せない
        'remember_token', // ログイン状態を保持するためのトークン
    ];

    /**
     * 型変換の設定
     * 例：email_verified_atを日付型として扱う
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // メール認証日時を日付型として扱う
        'password' => 'hashed',            // パスワードを自動的にハッシュ化する
    ];

    /**
     * 「一人のユーザーは、複数の患者を持っている」というルール
     * 例：ユーザー「田中花子」は「おじいちゃん」「おばあちゃん」を管理している
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
}
