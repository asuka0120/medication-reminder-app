<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicineRequest extends FormRequest
{
    /**
     * このリクエストを送る権限があるか判定する。
     * ログインユーザー自身の患者に対してのみ、お薬を登録できる。
     */
    public function authorize(): bool
    {
        $patient = Patient::find($this->patient_id);

        // patient_idが存在しない（空欄・不正な値を含む）場合は、ここでは弾かない。
        // Laravelはauthorize()をrules()より先に実行するため、ここでfalseを返すと
        // 「他人の患者(403)」と「存在しない患者(本来は422)」の区別ができなくなってしまう。
        // 存在チェックはrules()側のexistsルールに任せ、ここではtrueを返して素通りさせる。
        if (! $patient) {
            return true;
        }

        // 患者が実在する場合のみ、「ログインユーザー自身の患者か」を判定する。
        // 他人の患者であればfalse（403：あなたの患者ではありません、の意味）。
        return $this->user()->can('update', $patient);
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'patient_id' => [
                'required',
                'integer',
                // ゴミ箱（ソフトデリート）に入っている患者のIDは「存在しない」扱いにする
                Rule::exists('patients', 'id')->whereNull('deleted_at'),
            ],
            'medicine_name' => 'required|string|max:50',
            'dosage_select' => 'required|string',
            // 「その他」を選んだときだけ、手入力欄を必須にする（条件付きバリデーション）
            'dosage_manual' => 'nullable|required_if:dosage_select,other|string|max:100',
            'times' => 'nullable|array',
            // 空欄（未使用の自由入力欄）は許容しつつ、値がある場合はHH:MM形式であることを検証する
            'times.*' => 'nullable|date_format:H:i',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
