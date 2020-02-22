<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use UsesUuid;
    protected $fillable = [
        'title', 'path', 'user_id', 'size',
    ];

    public $appends = ['url', 'link', 'uploaded_time', 'size_in_kb'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($image) {
            $image->user_id = auth()->user()->id;
        });
    }

    public function getLinkAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }

    public function getUploadedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getSizeInKbAttribute()
    {
        return round($this->size / 1024, 2);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
