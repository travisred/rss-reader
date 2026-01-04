<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feed_item_id',
        'is_read',
        'is_starred',
        'read_at',
        'starred_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'read_at' => 'datetime',
        'starred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedItem()
    {
        return $this->belongsTo(FeedItem::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function toggleStar()
    {
        $this->update([
            'is_starred' => !$this->is_starred,
            'starred_at' => !$this->is_starred ? now() : null,
        ]);
    }
}
