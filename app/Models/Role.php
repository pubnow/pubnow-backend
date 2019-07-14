<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;

class Role extends Model
{
    use UsesUuid;
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
