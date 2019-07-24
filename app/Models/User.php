<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Concerns\UsesUuid;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use UsesUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name',
        'email', 'password',
        'bio', 'avatar', 'role_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Set the password using bcrypt hash.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = (password_get_info($value)['algo'] === 0) ? bcrypt($value) : $value;
    }

    /**
     * Get the key name for route model binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function organizationsFollowed() {
        return $this->belongsToMany(Organization::class, 'user_follow_organizations');
    }

    // Users who followed this user
    public function followers() {
        return $this->belongsToMany(User::class, 'user_follow_users', 'user_id', 'followed');
    }

    // Users who this user followed
    public function usersFollowed() {
        return $this->belongsToMany(User::class, 'user_follow_users', 'followed', 'user_id');
    }

    public function isAdmin()
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->name === 'admin';
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
