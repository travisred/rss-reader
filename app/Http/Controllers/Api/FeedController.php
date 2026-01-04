<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\UserFeed;
use App\Services\FeedFetcherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedController extends Controller
{
    protected $feedFetcher;

    public function __construct(FeedFetcherService $feedFetcher)
    {
        $this->feedFetcher = $feedFetcher;
    }

    public function index(Request $request)
    {
        $feeds = $request->user()->feeds()
            ->withCount(['items as unread_count' => function ($query) use ($request) {
                $query->whereHas('userItems', function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id)
                      ->where('is_read', false);
                });
            }])
            ->get()
            ->map(function ($feed) use ($request) {
                $userFeed = $feed->pivot;
                return [
                    'id' => $feed->id,
                    'url' => $feed->url,
                    'title' => $userFeed->custom_title ?: $feed->title,
                    'description' => $feed->description,
                    'site_url' => $feed->site_url,
                    'favicon_url' => $feed->favicon_url,
                    'folder' => $userFeed->folder,
                    'order' => $userFeed->order,
                    'unread_count' => $feed->unread_count,
                    'last_fetched_at' => $feed->last_fetched_at,
                ];
            });

        return response()->json(['feeds' => $feeds]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if feed already exists
        $feed = Feed::where('url', $request->url)->first();

        if (!$feed) {
            // Fetch and validate the feed
            try {
                $feedData = $this->feedFetcher->fetch($request->url);
                
                $feed = Feed::create([
                    'url' => $request->url,
                    'title' => $feedData['title'] ?? 'Untitled Feed',
                    'description' => $feedData['description'] ?? null,
                    'site_url' => $feedData['site_url'] ?? null,
                    'favicon_url' => $feedData['favicon_url'] ?? null,
                    'last_fetched_at' => now(),
                ]);

                // Store initial items
                if (!empty($feedData['items'])) {
                    $this->feedFetcher->storeItems($feed, $feedData['items']);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Could not fetch feed: ' . $e->getMessage()
                ], 422);
            }
        }

        // Check if user already subscribed
        $existingSubscription = UserFeed::where('user_id', $request->user()->id)
            ->where('feed_id', $feed->id)
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'error' => 'You are already subscribed to this feed'
            ], 422);
        }

        // Subscribe user to feed
        $userFeed = UserFeed::create([
            'user_id' => $request->user()->id,
            'feed_id' => $feed->id,
            'folder' => $request->folder,
            'order' => $request->user()->userFeeds()->count(),
        ]);

        return response()->json([
            'message' => 'Feed added successfully',
            'feed' => [
                'id' => $feed->id,
                'url' => $feed->url,
                'title' => $feed->title,
                'folder' => $userFeed->folder,
            ]
        ], 201);
    }

    public function update(Request $request, Feed $feed)
    {
        $userFeed = UserFeed::where('user_id', $request->user()->id)
            ->where('feed_id', $feed->id)
            ->firstOrFail();

        $request->validate([
            'custom_title' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:255',
        ]);

        $userFeed->update($request->only(['custom_title', 'folder']));

        return response()->json([
            'message' => 'Feed updated successfully',
            'feed' => $userFeed
        ]);
    }

    public function destroy(Request $request, Feed $feed)
    {
        $userFeed = UserFeed::where('user_id', $request->user()->id)
            ->where('feed_id', $feed->id)
            ->firstOrFail();

        $userFeed->delete();

        return response()->json(['message' => 'Feed removed successfully']);
    }

    public function refresh(Request $request, Feed $feed)
    {
        try {
            $this->feedFetcher->fetchFeed($feed);
            
            return response()->json([
                'message' => 'Feed refreshed successfully',
                'last_fetched_at' => $feed->fresh()->last_fetched_at
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Could not refresh feed: ' . $e->getMessage()
            ], 500);
        }
    }
}
