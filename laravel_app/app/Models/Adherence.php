<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adherence extends Model
{
    use HasFactory;

    // まとめてデータを保存することを許可する設定
    protected $fillable = ['medicine_id', 'taken_date', 'note']; // note を追加

    // どのお薬の記録なのかを紐付ける設定
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}