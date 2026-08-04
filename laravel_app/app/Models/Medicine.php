<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ゴミ箱機能：誤って削除すると、これまでの服用時間や記録も一緒に失われてしまうため

class Medicine extends Model
{
    use HasFactory;
    use SoftDeletes; // ゴミ箱機能：誤って削除すると、これまでの服用時間や記録も一緒に失われてしまうため

    /**
     * 一括割り当て（createメソッドなど）を許可する項目
     */
    protected $fillable = [
        'patient_id',    // 患者ID
        'medicine_name', // お薬名
        'dosage',        // 分量・飲み方
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
     * リレーション設定：一つのお薬は、たくさんの服用スケジュール（何時に飲むか）を持っている
     */
    public function schedules()
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    /**
     * リレーション設定：このお薬の服薬記録を、スケジュール（何時の分か）をまたいでまとめて取得する
     * 例：ロキソニンの「朝の分」「晩の分」、両方の服薬記録を合わせて取得できる
     */
    public function adherences()
    {
        return $this->hasManyThrough(Adherence::class, MedicineSchedule::class);
    }
}
