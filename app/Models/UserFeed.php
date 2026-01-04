<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feed_id',
        'custom_title',
        'folder',
        'order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    public function getDisplayTitle()
    {
        return $this->custom_title ?: $this->feed->title;
    }
}
