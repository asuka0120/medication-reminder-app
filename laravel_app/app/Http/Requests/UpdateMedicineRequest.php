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

        return $medicine && $medicine->patient->user_id === auth()->id();
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'medicine_name' => 'required|string|max:50',
            'timings' => 'required|array',
            'dosage_select' => 'required',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
