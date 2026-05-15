<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * 登録・編集を許可する列の一覧
     * ここに書いていない列はデータベースに保存されない（セキュリティ対策）
     */
    protected $fillable = [
        'user_id',  // どのユーザーの患者か
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