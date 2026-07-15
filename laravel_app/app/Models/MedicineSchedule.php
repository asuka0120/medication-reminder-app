<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineSchedule extends Model
{
    use HasFactory;

    /**
     * 一括割り当て（createメソッドなど）を許可する項目
     */
    protected $fillable = [
        'medicine_id',
        'scheduled_time',
    ];

    /**
     * リレーション設定：このスケジュールは、特定の薬本体に属している
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * リレーション設定：一つのスケジュールは、たくさんの服用記録（adherences）を持っている
     */
    public function adherences()
    {
        return $this->hasMany(Adherence::class);
    }
}
