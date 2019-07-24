<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'owner', 'description', 'logo', 'active',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    public function followers() {
        return $this->belongsToMany(User::class, 'user_follow_organizations');
    }

}
