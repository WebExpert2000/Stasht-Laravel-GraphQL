<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    //
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
