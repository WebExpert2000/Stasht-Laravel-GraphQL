<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class InstagramUser extends Model
{
    //
    public function user(): HasOne
    {
        return $this->hasOne(\App\Models\User::class, 'ig_user_id', 'id');
    }
}
