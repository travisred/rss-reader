# Quick Start Guide

## Prerequisites
- Docker and Docker Compose installed on your system
- Git installed
- At least 2GB of available RAM

## Installation (Automated)

The easiest way to get started is using the automated setup script:

```bash
./setup.sh
```

This will:
1. Build all Docker containers
2. Install PHP and Node.js dependencies
3. Generate application keys
4. Run database migrations
5. Build frontend assets
6. Set correct permissions

After the script completes, visit: **http://localhost:8080**

## Installation (Manual)

If you prefer to run commands manually:

### 1. Start Docker Containers
```bash
docker-compose up -d
```

### 2. Install PHP Dependencies
```bash
docker-compose exec app composer install
```

### 3. Copy Environment File
```bash
cp .env.example .env
```

### 4. Generate Application Key
```bash
docker-compose exec app php artisan key:generate
```

### 5. Run Database Migrations
```bash
docker-compose exec app php artisan migrate
```

### 6. Install Node Dependencies
```bash
docker-compose exec app npm install
```

### 7. Build Frontend
```bash
docker-compose exec app npm run build
```

### 8. Set Permissions
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## First Steps

1. **Create an Account**
   - Navigate to http://localhost:8080
   - Click "Sign up" to create a new account
   - Enter your name, email, and password

2. **Add Your First Feed**
   - Click the "+ Add Feed" button
   - Enter an RSS feed URL (e.g., https://feeds.feedburner.com/TechCrunch/)
   - Optionally add a folder name to organize feeds
   - Click "Add Feed"

3. **Browse Articles**
   - Click on a feed in the sidebar to view its articles
   - Click on an article title to read the full content
   - Use the star icon to save articles for later
   - Articles are automatically marked as read when viewed

4. **Organize Your Feeds**
   - Use folders to group related feeds
   - View all unread items by clicking "All Items"
   - Access starred articles via "Starred Items"

## Example RSS Feeds to Try

Here are some popular RSS feeds you can add to get started:

- **TechCrunch**: https://feeds.feedburner.com/TechCrunch/
- **Hacker News**: https://news.ycombinator.com/rss
- **The Verge**: https://www.theverge.com/rss/index.xml
- **Ars Technica**: http://feeds.arstechnica.com/arstechnica/index
- **Reddit Programming**: https://www.reddit.com/r/programming/.rss

## Development Mode

To run the frontend in development mode with hot module replacement:

```bash
docker-compose exec app npm run dev
```

Then access the app at http://localhost:8080 (Vite will handle HMR on port 5173 internally).

## Common Commands

### View Application Logs
```bash
docker-compose logs -f app
```

### View All Container Logs
```bash
docker-compose logs -f
```

### Access Application Container Shell
```bash
docker-compose exec app bash
```

### Manually Fetch Feeds
```bash
docker-compose exec app php artisan feeds:fetch
```

### Restart Containers
```bash
docker-compose restart
```

### Stop All Containers
```bash
docker-compose down
```

### Rebuild Containers
```bash
docker-compose up -d --build
```

## Troubleshooting

### "Connection refused" when accessing the app
- Wait a minute for all containers to fully start
- Check if all containers are running: `docker-compose ps`
- Check nginx logs: `docker-compose logs nginx`

### Database connection errors
- Verify database container is running: `docker-compose ps db`
- Check database logs: `docker-compose logs db`
- Ensure database is fully initialized (can take 10-20 seconds on first start)

### Frontend not loading
- Clear browser cache
- Rebuild assets: `docker-compose exec app npm run build`
- Check for build errors: `docker-compose logs app`

### Permission errors
Run the permission fix command:
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Feeds not updating
- Check the scheduler logs: `docker-compose logs scheduler`
- Check the queue worker: `docker-compose logs queue`
- Manually trigger fetch: `docker-compose exec app php artisan feeds:fetch`

## Ports Used

- **8080**: Main application (nginx)
- **3306**: MySQL database
- **6379**: Redis
- **5173**: Vite dev server (development mode only)

## Next Steps

- Explore the API endpoints in the README.md
- Customize the appearance by editing Tailwind CSS
- Add more feeds and organize them into folders
- Star your favorite articles
- Mark articles as read/unread

## Support

If you encounter any issues:
1. Check the logs: `docker-compose logs -f`
2. Verify all containers are running: `docker-compose ps`
3. Try restarting: `docker-compose restart`
4. Rebuild if necessary: `docker-compose up -d --build`

Enjoy your RSS reader! 🎉
