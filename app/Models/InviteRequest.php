<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class InviteRequest extends Model
{
    use UsesUuid;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id', 'user_id', 'status',
    ];
    public function organization() {
        return $this->belongsTo(Organization::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
