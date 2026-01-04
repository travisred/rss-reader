<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function feeds()
    {
        return $this->belongsToMany(Feed::class, 'user_feeds')
            ->withPivot('custom_title', 'folder', 'order')
            ->withTimestamps();
    }

    public function userFeeds()
    {
        return $this->hasMany(UserFeed::class);
    }

    public function userFeedItems()
    {
        return $this->hasMany(UserFeedItem::class);
    }

    public function unreadCount()
    {
        return $this->userFeedItems()->where('is_read', false)->count();
    }

    public function starredCount()
    {
        return $this->userFeedItems()->where('is_starred', true)->count();
    }
}
