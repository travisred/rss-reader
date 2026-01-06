<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedItem;
use App\Models\UserFeedItem;
use Illuminate\Http\Request;

class FeedItemController extends Controller
{
    public function index(Request $request)
    {
        $query = FeedItem::query()
            ->whereHas('feed.users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            });

        // Filter by feed
        if ($request->has('feed_id')) {
            $query->where('feed_id', $request->feed_id);
        }

        // Filter by read/unread
        if ($request->has('is_read')) {
            $isRead = filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('userItems', function ($q) use ($request, $isRead) {
                $q->where('user_id', $request->user()->id)
                  ->where('is_read', $isRead);
            });
        }

        // Filter by starred
        if ($request->has('is_starred')) {
            $isStarred = filter_var($request->is_starred, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('userItems', function ($q) use ($request, $isStarred) {
                $q->where('user_id', $request->user()->id)
                  ->where('is_starred', $isStarred);
            });
        }

        // Filter by folder
        if ($request->has('folder')) {
            $query->whereHas('feed.userFeeds', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->where('folder', $request->folder);
            });
        }

        $items = $query->with(['feed', 'userItems' => function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            }])
            ->orderBy('published_at', 'desc')
            ->paginate(50);

        return response()->json($items);
    }

    public function show(Request $request, FeedItem $feedItem)
    {
        $feedItem->load(['feed', 'userItems' => function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        }]);

        // Automatically mark as read when viewed
        $userItem = $feedItem->userItems->first();
        if ($userItem && !$userItem->is_read) {
            $userItem->markAsRead();
            // Reload the relationship to get updated data
        } elseif (!$userItem) {
            // Create user item if it doesn't exist
            $userItem = UserFeedItem::create([
                'user_id' => $request->user()->id,
                'feed_item_id' => $feedItem->id,
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        $feedItem->load(['userItems' => function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        }]);
        return response()->json($feedItem);
    }

    public function markAsRead(Request $request, FeedItem $feedItem)
    {
        $userItem = UserFeedItem::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'feed_item_id' => $feedItem->id,
            ],
            [
                'is_read' => false,
            ]
        );

        $userItem->markAsRead();

        return response()->json(['message' => 'Item marked as read']);
    }

    public function markAsUnread(Request $request, FeedItem $feedItem)
    {
        $userItem = UserFeedItem::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'feed_item_id' => $feedItem->id,
            ],
            [
                'is_read' => true,
            ]
        );

        $userItem->markAsUnread();

        return response()->json(['message' => 'Item marked as unread']);
    }

    public function toggleStar(Request $request, FeedItem $feedItem)
    {
        $userItem = UserFeedItem::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'feed_item_id' => $feedItem->id,
            ],
            [
                'is_read' => false,
                'is_starred' => false,
            ]
        );

        $userItem->toggleStar();

        return response()->json([
            'message' => $userItem->is_starred ? 'Item starred' : 'Item unstarred',
            'is_starred' => $userItem->is_starred,
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->validate([
            'feed_id' => 'nullable|exists:feeds,id',
        ]);

        $query = FeedItem::query()
            ->whereHas('feed.users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            });

        if ($request->has('feed_id')) {
            $query->where('feed_id', $request->feed_id);
        }

        $items = $query->get();

        foreach ($items as $item) {
            $userItem = UserFeedItem::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'feed_item_id' => $item->id,
                ],
                [
                    'is_read' => false,
                ]
            );

            if (!$userItem->is_read) {
                $userItem->markAsRead();
            }
        }

        return response()->json([
            'message' => 'All items marked as read',
            'count' => $items->count()
        ]);
    }
}
