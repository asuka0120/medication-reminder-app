<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * ログインユーザー自身が登録した患者かどうかを判定する共通ロジック
     */
    private function isOwner(User $user, Patient $patient): bool
    {
        return $user->id === $patient->user_id;
    }

    /**
     * 患者の詳細（服用履歴カレンダーなど）を閲覧できるか
     */
    public function view(User $user, Patient $patient): bool
    {
        return $this->isOwner($user, $patient);
    }

    /**
     * 患者情報を更新できるか
     */
    public function update(User $user, Patient $patient): bool
    {
        return $this->isOwner($user, $patient);
    }

    /**
     * 患者情報を削除（ゴミ箱へ移動）できるか
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $this->isOwner($user, $patient);
    }
}
