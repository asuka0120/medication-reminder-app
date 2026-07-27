<?php

namespace App\Http\Requests;

use App\Models\Medicine;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineRequest extends FormRequest
{
    /**
     * このリクエストを送る権限があるか判定する。
     * ログインユーザー自身の患者の薬に対してのみ、更新できる。
     */
    public function authorize(): bool
    {
        $medicine = Medicine::find($this->route('medicine'));

        return $medicine && $this->user()->can('update', $medicine);
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'medicine_name' => 'required|string|max:50',
            'timings' => 'required|array',
            // 空欄（未使用の自由入力欄）は許容しつつ、値がある場合はHH:MM形式であることを検証する
            'timings.*' => 'nullable|date_format:H:i',
            'dosage_select' => 'required|string',
            // 「その他」を選んだときだけ、手入力欄を必須にする（条件付きバリデーション）
            'dosage_manual' => 'nullable|required_if:dosage_select,other|string|max:100',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
