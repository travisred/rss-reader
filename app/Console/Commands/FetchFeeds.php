<?php

namespace App\Console\Commands;

use App\Services\FeedFetcherService;
use Illuminate\Console\Command;

class FetchFeeds extends Command
{
    protected $signature = 'feeds:fetch {--feed-id= : Specific feed ID to fetch}';
    protected $description = 'Fetch RSS feeds and update items';

    public function handle(FeedFetcherService $feedFetcher)
    {
        $this->info('Starting feed fetch...');

        if ($feedId = $this->option('feed-id')) {
            $feed = \App\Models\Feed::findOrFail($feedId);
            try {
                $feedFetcher->fetchFeed($feed);
                $this->info("Successfully fetched feed: {$feed->title}");
            } catch (\Exception $e) {
                $this->error("Failed to fetch feed {$feed->id}: {$e->getMessage()}");
            }
        } else {
            $feedFetcher->fetchAllFeeds();
            $this->info('Feed fetch completed');
        }

        return 0;
    }
}
