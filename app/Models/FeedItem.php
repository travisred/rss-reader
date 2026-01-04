<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_id',
        'guid',
        'title',
        'description',
        'content',
        'url',
        'author',
        'published_at',
        'categories',
        'thumbnail_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'categories' => 'array',
    ];

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    public function userItems()
    {
        return $this->hasMany(UserFeedItem::class);
    }

    public function userItem(User $user)
    {
        return $this->userItems()->where('user_id', $user->id)->first();
    }
}
