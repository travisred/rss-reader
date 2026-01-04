<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'title',
        'description',
        'site_url',
        'favicon_url',
        'last_fetched_at',
        'last_modified_at',
        'etag',
        'fetch_interval',
        'is_active',
        'error_message',
        'error_count',
    ];

    protected $casts = [
        'last_fetched_at' => 'datetime',
        'last_modified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_feeds')
            ->withPivot('custom_title', 'folder', 'order')
            ->withTimestamps();
    }

    public function userFeeds()
    {
        return $this->hasMany(UserFeed::class);
    }

    public function items()
    {
        return $this->hasMany(FeedItem::class);
    }

    public function needsFetch()
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_fetched_at) {
            return true;
        }

        return $this->last_fetched_at->addMinutes($this->fetch_interval) <= now();
    }
}
