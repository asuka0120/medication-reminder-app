<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    /**
     * このリクエストを送る権限があるか判定する。
     * ログインユーザー自身の患者に対してのみ、お薬を登録できる。
     */
    public function authorize(): bool
    {
        $patient = Patient::find($this->patient_id);

        return $patient && $this->user()->can('update', $patient);
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'medicine_name' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'medicine_name.required' => '薬名は必須です。',
            'medicine_name.max' => '薬名は50文字以内で入力してください。',
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '対応していないファイル形式です。JPEGまたはPNGをアップロードしてください。',
            'image.max' => 'ファイルサイズは2MB以下にしてください。',
        ];
    }
}
