# RSS Reader - Google Reader Style

A modern RSS feed reader built with Laravel, Vue.js, Tailwind CSS, and MySQL, inspired by Google Reader.

## Features

- 🔐 User registration and authentication
- 📰 Subscribe to RSS/Atom feeds
- 📁 Organize feeds into folders
- ⭐ Star important articles
- ✅ Mark articles as read/unread
- 🔄 Automatic feed refreshing
- 🎨 Clean, Google Reader-inspired UI
- 🐳 Fully containerized with Docker

## Tech Stack

- **Backend**: Laravel 10 (PHP 8.2)
- **Frontend**: Vue.js 3 with Composition API
- **Styling**: Tailwind CSS
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis
- **Containerization**: Docker & Docker Compose

## Getting Started

### Prerequisites

- Docker and Docker Compose installed
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd rss_reader
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Build and start Docker containers**
   ```bash
   docker-compose up -d
   ```

4. **Install PHP dependencies**
   ```bash
   docker-compose exec app composer install
   ```

5. **Generate application key**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

7. **Install Node.js dependencies**
   ```bash
   docker-compose exec app npm install
   ```

8. **Build frontend assets**
   ```bash
   docker-compose exec app npm run build
   ```

   Or for development with hot reload:
   ```bash
   docker-compose exec app npm run dev
   ```

9. **Access the application**
   
   Open your browser and navigate to: `http://localhost:8080`

## Development

### Running the development server

For frontend development with hot module replacement:

```bash
docker-compose exec app npm run dev
```

### Manually fetch feeds

```bash
docker-compose exec app php artisan feeds:fetch
```

To fetch a specific feed:

```bash
docker-compose exec app php artisan feeds:fetch --feed-id=1
```

### Queue worker

The queue worker runs automatically in the Docker container to process background jobs.

### Scheduler

The scheduler runs automatically and fetches feeds every 15 minutes.

## Docker Services

The application consists of several Docker containers:

- **app** - PHP-FPM application server
- **nginx** - Web server
- **db** - MySQL database
- **redis** - Cache and queue backend
- **queue** - Laravel queue worker
- **scheduler** - Laravel task scheduler

### Useful Docker Commands

```bash
# View logs
docker-compose logs -f

# Access app container shell
docker-compose exec app bash

# Stop all containers
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View running containers
docker-compose ps
```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/logout` - Logout (requires auth)
- `GET /api/me` - Get current user info (requires auth)

### Feeds
- `GET /api/feeds` - List user's feeds
- `POST /api/feeds` - Add new feed
- `PUT /api/feeds/{id}` - Update feed
- `DELETE /api/feeds/{id}` - Remove feed
- `POST /api/feeds/{id}/refresh` - Manually refresh feed

### Feed Items
- `GET /api/items` - List feed items (supports filtering)
- `GET /api/items/{id}` - Get single item
- `POST /api/items/{id}/read` - Mark as read
- `POST /api/items/{id}/unread` - Mark as unread
- `POST /api/items/{id}/star` - Toggle star
- `POST /api/items/mark-all-read` - Mark all as read

## Database Schema

### Tables

- **users** - User accounts
- **feeds** - RSS feed sources
- **user_feeds** - User feed subscriptions
- **feed_items** - Individual feed items/articles
- **user_feed_items** - User-specific item state (read, starred)
- **personal_access_tokens** - API authentication tokens

## Configuration

### Environment Variables

Key configuration options in `.env`:

- `DB_*` - Database connection settings
- `REDIS_*` - Redis connection settings
- `RSS_FETCH_INTERVAL` - How often to fetch feeds (minutes)
- `RSS_MAX_ITEMS_PER_FEED` - Maximum items to store per feed

## Troubleshooting

### Permission Issues

If you encounter permission issues:

```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues

Make sure the database container is fully started:

```bash
docker-compose logs db
```

Wait for the message: "ready for connections"

### Frontend Not Loading

Rebuild assets:

```bash
docker-compose exec app npm run build
```

### Feeds Not Updating

Check the scheduler and queue worker:

```bash
docker-compose logs scheduler
docker-compose logs queue
```

## Contributing

Feel free to submit issues and enhancement requests!

## License

MIT License
