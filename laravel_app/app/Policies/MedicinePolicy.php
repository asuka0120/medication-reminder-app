<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;

class MedicinePolicy
{
    /**
     * ログインユーザー自身の患者に紐づく薬かどうかを判定する共通ロジック
     */
    private function isOwner(User $user, Medicine $medicine): bool
    {
        return $user->id === $medicine->patient->user_id;
    }

    /**
     * お薬の編集画面を閲覧できるか
     */
    public function view(User $user, Medicine $medicine): bool
    {
        return $this->isOwner($user, $medicine);
    }

    /**
     * お薬情報を更新できるか
     */
    public function update(User $user, Medicine $medicine): bool
    {
        return $this->isOwner($user, $medicine);
    }

    /**
     * お薬情報を削除（ゴミ箱へ移動）できるか
     */
    public function delete(User $user, Medicine $medicine): bool
    {
        return $this->isOwner($user, $medicine);
    }
}
