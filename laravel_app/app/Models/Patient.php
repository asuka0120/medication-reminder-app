<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // 「一人の患者さんは、複数のお薬（medicines）を持っている」というルール
    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }
}