#!/bin/bash

# RSS Reader Setup Script

echo "🚀 Setting up RSS Reader..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Build and start containers
echo "📦 Building Docker containers..."
docker-compose up -d --build

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Install PHP dependencies
echo "📥 Installing PHP dependencies..."
docker-compose exec -T app composer install --no-interaction

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate --force

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Install Node dependencies
echo "📥 Installing Node.js dependencies..."
docker-compose exec -T app npm install

# Build frontend assets
echo "🎨 Building frontend assets..."
docker-compose exec -T app npm run build

# Set permissions
echo "🔒 Setting permissions..."
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec -T app chmod -R 775 storage bootstrap/cache

echo "✅ Setup complete!"
echo ""
echo "🌐 Application is running at: http://localhost:8080"
echo ""
echo "📝 Useful commands:"
echo "  docker-compose logs -f          # View logs"
echo "  docker-compose exec app bash    # Access app container"
echo "  docker-compose down             # Stop all containers"
echo ""
