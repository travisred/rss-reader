<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FeedFetcherService
{
    public function fetch(string $url): array
    {
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch feed: HTTP ' . $response->status());
        }

        $xml = $response->body();
        
        // Try to parse as RSS/Atom
        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml);
        
        if ($feed === false) {
            throw new \Exception('Invalid feed format');
        }

        // Detect feed type
        if ($feed->channel) {
            return $this->parseRss($feed, $url);
        } elseif ($feed->entry) {
            return $this->parseAtom($feed, $url);
        }

        throw new \Exception('Unknown feed format');
    }

    protected function parseRss(\SimpleXMLElement $xml, string $feedUrl): array
    {
        $channel = $xml->channel;
        
        $feedData = [
            'title' => (string) $channel->title,
            'description' => (string) $channel->description,
            'site_url' => (string) $channel->link,
            'favicon_url' => $this->extractFavicon($channel),
            'items' => [],
        ];

        foreach ($channel->item as $item) {
            $feedData['items'][] = $this->parseRssItem($item, $feedUrl);
        }

        return $feedData;
    }

    protected function parseRssItem(\SimpleXMLElement $item, string $feedUrl): array
    {
        $guid = (string) ($item->guid ?: $item->link);
        $guid = $guid ?: Str::uuid();

        $description = (string) $item->description;
        $content = null;

        // Check for content:encoded
        $namespaces = $item->getNamespaces(true);
        if (isset($namespaces['content'])) {
            $content = (string) $item->children($namespaces['content'])->encoded;
        }

        // Extract categories
        $categories = [];
        foreach ($item->category as $category) {
            $categories[] = (string) $category;
        }

        // Extract thumbnail
        $thumbnail = null;
        if (isset($namespaces['media'])) {
            $media = $item->children($namespaces['media']);
            if ($media->thumbnail) {
                $thumbnail = (string) $media->thumbnail->attributes()->url;
            } elseif ($media->content) {
                $thumbnail = (string) $media->content->attributes()->url;
            }
        }

        return [
            'guid' => $guid,
            'title' => (string) $item->title,
            'description' => $description,
            'content' => $content ?: $description,
            'url' => (string) $item->link,
            'author' => (string) ($item->author ?: $item->children('dc', true)->creator),
            'published_at' => $this->parseDate((string) ($item->pubDate ?: $item->children('dc', true)->date)),
            'categories' => $categories,
            'thumbnail_url' => $thumbnail,
        ];
    }

    protected function parseAtom(\SimpleXMLElement $xml, string $feedUrl): array
    {
        $feedData = [
            'title' => (string) $xml->title,
            'description' => (string) $xml->subtitle,
            'site_url' => $this->extractAtomLink($xml),
            'favicon_url' => null,
            'items' => [],
        ];

        foreach ($xml->entry as $entry) {
            $feedData['items'][] = $this->parseAtomEntry($entry, $feedUrl);
        }

        return $feedData;
    }

    protected function parseAtomEntry(\SimpleXMLElement $entry, string $feedUrl): array
    {
        $guid = (string) $entry->id;
        $guid = $guid ?: Str::uuid();

        $content = (string) ($entry->content ?: $entry->summary);

        // Extract categories
        $categories = [];
        foreach ($entry->category as $category) {
            $categories[] = (string) $category->attributes()->term;
        }

        return [
            'guid' => $guid,
            'title' => (string) $entry->title,
            'description' => (string) $entry->summary,
            'content' => $content,
            'url' => $this->extractAtomLink($entry),
            'author' => (string) $entry->author->name,
            'published_at' => $this->parseDate((string) ($entry->published ?: $entry->updated)),
            'categories' => $categories,
            'thumbnail_url' => null,
        ];
    }

    protected function extractAtomLink(\SimpleXMLElement $element): ?string
    {
        foreach ($element->link as $link) {
            $rel = (string) $link->attributes()->rel;
            if ($rel === 'alternate' || !$rel) {
                return (string) $link->attributes()->href;
            }
        }
        return null;
    }

    protected function extractFavicon(\SimpleXMLElement $channel): ?string
    {
        // Try to find favicon in feed
        $namespaces = $channel->getNamespaces(true);
        if (isset($namespaces['atom'])) {
            $atom = $channel->children($namespaces['atom']);
            if ($atom->icon) {
                return (string) $atom->icon;
            }
        }

        return null;
    }

    protected function parseDate(?string $date): ?\DateTime
    {
        if (!$date) {
            return null;
        }

        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function fetchFeed(Feed $feed): void
    {
        try {
            $feedData = $this->fetch($feed->url);

            // Update feed metadata
            $feed->update([
                'title' => $feedData['title'] ?? $feed->title,
                'description' => $feedData['description'] ?? $feed->description,
                'site_url' => $feedData['site_url'] ?? $feed->site_url,
                'favicon_url' => $feedData['favicon_url'] ?? $feed->favicon_url,
                'last_fetched_at' => now(),
                'error_message' => null,
                'error_count' => 0,
            ]);

            // Store new items
            if (!empty($feedData['items'])) {
                $this->storeItems($feed, $feedData['items']);
            }
        } catch (\Exception $e) {
            $feed->update([
                'error_message' => $e->getMessage(),
                'error_count' => $feed->error_count + 1,
                'last_fetched_at' => now(),
            ]);

            // Deactivate feed after too many errors
            if ($feed->error_count >= 10) {
                $feed->update(['is_active' => false]);
            }

            throw $e;
        }
    }

    public function storeItems(Feed $feed, array $items): void
    {
        foreach ($items as $itemData) {
            FeedItem::updateOrCreate(
                [
                    'feed_id' => $feed->id,
                    'guid' => $itemData['guid'],
                ],
                [
                    'title' => $itemData['title'],
                    'description' => $itemData['description'],
                    'content' => $itemData['content'],
                    'url' => $itemData['url'],
                    'author' => $itemData['author'],
                    'published_at' => $itemData['published_at'],
                    'categories' => $itemData['categories'],
                    'thumbnail_url' => $itemData['thumbnail_url'],
                ]
            );
        }
    }

    public function fetchAllFeeds(): void
    {
        $feeds = Feed::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_fetched_at')
                    ->orWhereRaw('last_fetched_at + INTERVAL fetch_interval MINUTE <= NOW()');
            })
            ->get();

        foreach ($feeds as $feed) {
            try {
                $this->fetchFeed($feed);
            } catch (\Exception $e) {
                \Log::error("Failed to fetch feed {$feed->id}: {$e->getMessage()}");
            }
        }
    }
}
