<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Concerns\UsesUuid;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Scout\Searchable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use UsesUuid;
    use Searchable;

    public static function boot()
    {
        parent::boot();


        static::deleting(function ($user) {
            // before delete() method call this
            $user->followingTags()->detach();
            $user->followingCategories()->detach();
            $user->comments()->delete();
            $user->articles()->delete();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name',
        'email', 'password',
        'bio', 'image_id', 'role_id',
    ];

    public $append = ['avatar_url'];

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
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        return array('name' => $array['name'], 'username' => $array['username'], 'email' => $array['email']);
    }

    /**
     * Set the password using bcrypt hash.
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = (password_get_info($value)['algo'] === 0) ? bcrypt($value) : $value;
    }

    public function getAvatarUrlAttribute()
    {
        return $this->image ? $this->image->url : 'https://i.imgur.com/DoPMECx.jpg';
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

    public function isAdmin()
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->name === 'admin';
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function claps()
    {
        return $this->hasMany(Clap::class);
    }

    public function followingTags()
    {
        return $this->belongsToMany(Tag::class, 'user_follow_tags');
    }

    public function followingCategories()
    {
        return $this->belongsToMany(Category::class, 'user_follow_categories');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class)->latest();
    }

    public function series()
    {
        return $this->hasMany(Series::class);
    }

    // Users who followed this user
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow_users', 'followed', 'user_id');
    }

    // Users who this user followed
    public function followingUsers()
    {
        return $this->belongsToMany(User::class, 'user_follow_users', 'user_id', 'followed');
    }

    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }

    public function inviteRequests()
    {
        return $this->hasMany(InviteRequest::class)->where('status', 'pending');
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'invite_requests')->whereRaw("invite_requests.status = 'accepted'");
    }

    public function followingOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'user_follow_organizations');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
