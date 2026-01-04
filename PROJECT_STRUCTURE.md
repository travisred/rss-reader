# RSS Reader - Project Structure

## Complete File Structure

```
rss_reader/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── FetchFeeds.php          # Command to fetch RSS feeds
│   │   └── Kernel.php                  # Console kernel with scheduler
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php      # Authentication endpoints
│   │   │   │   ├── FeedController.php      # Feed management
│   │   │   │   └── FeedItemController.php  # Feed item operations
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   ├── EncryptCookies.php
│   │   │   └── VerifyCsrfToken.php
│   │   └── Kernel.php                  # HTTP kernel
│   ├── Models/
│   │   ├── Feed.php                    # Feed model
│   │   ├── FeedItem.php               # Feed item model
│   │   ├── User.php                    # User model
│   │   ├── UserFeed.php               # User-feed relationship
│   │   └── UserFeedItem.php           # User-item relationship
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── FeedFetcherService.php     # RSS fetching logic
├── bootstrap/
│   ├── app.php                         # Bootstrap application
│   └── providers.php
├── config/
│   ├── app.php                         # Application config
│   ├── auth.php                        # Authentication config
│   ├── broadcasting.php
│   ├── cache.php                       # Cache config
│   ├── cors.php                        # CORS config
│   ├── database.php                    # Database config
│   ├── logging.php                     # Logging config
│   ├── queue.php                       # Queue config
│   ├── sanctum.php                     # Sanctum API auth
│   └── session.php                     # Session config
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_users_table.php
│       ├── 2024_01_01_000002_create_feeds_table.php
│       ├── 2024_01_01_000003_create_user_feeds_table.php
│       ├── 2024_01_01_000004_create_feed_items_table.php
│       ├── 2024_01_01_000005_create_user_feed_items_table.php
│       └── 2024_01_01_000006_create_personal_access_tokens_table.php
├── docker/
│   ├── nginx/
│   │   └── conf.d/
│   │       └── app.conf                # Nginx configuration
│   ├── php/
│   │   └── local.ini                   # PHP configuration
│   └── Dockerfile                      # PHP container image
├── public/
│   ├── index.php                       # Application entry point
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css                     # Tailwind CSS styles
│   ├── js/
│   │   ├── components/                 # (Reserved for future components)
│   │   ├── pages/
│   │   │   ├── Home.vue               # Main reader interface
│   │   │   ├── Login.vue              # Login page
│   │   │   └── Register.vue           # Registration page
│   │   ├── router/
│   │   │   └── index.js               # Vue Router config
│   │   ├── stores/
│   │   │   ├── auth.js                # Auth state management
│   │   │   └── feed.js                # Feed state management
│   │   ├── App.vue                     # Root Vue component
│   │   ├── app.js                      # Vue app entry
│   │   └── bootstrap.js                # Axios configuration
│   └── views/
│       └── app.blade.php               # Main HTML template
├── routes/
│   ├── api.php                         # API routes
│   ├── console.php                     # Console commands
│   └── web.php                         # Web routes
├── storage/
│   ├── app/
│   │   └── public/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
├── .dockerignore                       # Docker ignore file
├── .env.example                        # Environment template
├── .gitattributes
├── .gitignore
├── artisan                             # Laravel CLI
├── composer.json                       # PHP dependencies
├── docker-compose.yml                  # Docker orchestration
├── package.json                        # Node.js dependencies
├── phpunit.xml                         # PHPUnit configuration
├── postcss.config.js                   # PostCSS config
├── QUICKSTART.md                       # Quick start guide
├── README.md                           # Main documentation
├── setup.sh                            # Automated setup script
├── tailwind.config.js                  # Tailwind CSS config
└── vite.config.js                      # Vite build config
```

## Key Components

### Backend (Laravel)

**Models**
- `User` - User authentication and profile
- `Feed` - RSS feed sources with metadata
- `FeedItem` - Individual articles from feeds
- `UserFeed` - User's feed subscriptions with customization
- `UserFeedItem` - User-specific item state (read, starred)

**Controllers**
- `AuthController` - Register, login, logout, user info
- `FeedController` - CRUD operations for feeds
- `FeedItemController` - Item listing, marking read/unread, starring

**Services**
- `FeedFetcherService` - RSS/Atom parsing and storage

**Commands**
- `feeds:fetch` - Manual feed fetching command

**Jobs & Scheduling**
- Automatic feed fetching every 15 minutes via scheduler
- Queue worker for background processing

### Frontend (Vue.js)

**Pages**
- `Login.vue` - User authentication
- `Register.vue` - New user registration
- `Home.vue` - Main RSS reader interface (Google Reader style)

**Stores (Pinia)**
- `auth` - Authentication state
- `feed` - Feed and item management

**Router**
- Route protection (auth required)
- Guest-only routes (login/register)

### Infrastructure (Docker)

**Containers**
- `app` - PHP-FPM 8.2
- `nginx` - Web server
- `db` - MySQL 8.0
- `redis` - Cache and queue
- `queue` - Laravel queue worker
- `scheduler` - Laravel task scheduler

## Data Flow

### Adding a Feed
1. User submits feed URL via Vue frontend
2. `FeedController@store` validates and processes request
3. `FeedFetcherService` fetches and parses RSS/Atom feed
4. Feed metadata and items stored in database
5. User subscription created in `user_feeds` table
6. Frontend refreshes to show new feed

### Reading Articles
1. User selects feed from sidebar
2. `FeedItemController@index` returns paginated items
3. User clicks article to view full content
4. `FeedItemController@show` marks as read automatically
5. Article content displayed in main pane
6. User can star or mark unread as needed

### Automatic Updates
1. Scheduler triggers every 15 minutes
2. `feeds:fetch` command runs
3. Active feeds with expired intervals fetched
4. New items added to database
5. Users see new items on next refresh

## Database Schema

**Key Relationships**
- User → UserFeed → Feed (many-to-many with pivot)
- Feed → FeedItem (one-to-many)
- User → UserFeedItem → FeedItem (tracks read/starred state)

**Indexes**
- Feed items by feed_id and published_at
- User feed items by user_id and is_read
- User feed items by user_id and is_starred

## API Authentication

- Uses Laravel Sanctum for token-based auth
- Tokens stored in `personal_access_tokens` table
- Bearer token authentication for all protected routes
- Automatic token expiration and cleanup

## Caching Strategy

- Redis for session storage
- Redis for cache backend
- Redis for queue backend
- Fast response times and scalability

## Deployment Considerations

- All services containerized for consistency
- Horizontal scaling possible for app, queue, scheduler
- Database can be moved to managed service
- Static assets served via nginx
- Environment-based configuration via .env

## Future Enhancements

Potential additions to consider:
- Full-text search across articles
- Feed categories/tags
- Import/export OPML
- Keyboard shortcuts
- Dark mode theme
- Mobile responsive improvements
- Push notifications for new articles
- Social sharing features
- Article recommendations
- Multi-language support
