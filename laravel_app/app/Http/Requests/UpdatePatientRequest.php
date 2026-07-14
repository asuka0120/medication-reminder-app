<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * このリクエストを送る権限があるか判定する。
     * ログインユーザー自身の患者に対してのみ、更新できる。
     */
    public function authorize(): bool
    {
        $patient = Patient::find($this->route('patient'));

        return $patient && $this->user()->can('update', $patient);
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'name.required' => '名前は必須です。',
            'name.max' => '名前は50文字以内で入力してください。',
        ];
    }
}
