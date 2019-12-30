<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class FacebookUser extends Model
{
    //
    public function user(): HasOne
    {
        return $this->hasOne(\App\Models\User::class, 'fb_user_id', 'id');
    }
}
