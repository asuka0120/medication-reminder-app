<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ゴミ箱機能用

class Medicine extends Model
{
    use HasFactory;
    use SoftDeletes; // ゴミ箱機能（論理削除）を有効化

    /**
     * 一括割り当て（createメソッドなど）を許可する項目
     * ここに patient_id が抜けていたため、エラーが発生していました。
     */
    protected $fillable = [
        'patient_id',    // 患者ID
        'medicine_name', // お薬名
        'dosage',        // 分量・飲み方
        'scheduled_time', // 服用時刻
        'image_path',    // 画像の保存パス
    ];

    /**
     * リレーション設定：このお薬は特定の患者さんに属している
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * リレーション設定：一つのお薬は、たくさんの服用記録（adherences）を持っている
     */
    public function adherences()
    {
        return $this->hasMany(Adherence::class);
    }
}
