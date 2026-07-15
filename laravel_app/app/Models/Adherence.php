<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adherence extends Model
{
    use HasFactory;

    // まとめてデータを保存することを許可する設定
    // ※medicine_id は後方互換のための予備列（実質的な参照先は medicine_schedule_id）
    protected $fillable = ['medicine_id', 'medicine_schedule_id', 'taken_date', 'taken_time', 'note'];

    // どの服薬スケジュールの記録なのかを紐付ける設定
    public function schedule()
    {
        return $this->belongsTo(MedicineSchedule::class, 'medicine_schedule_id');
    }
}
