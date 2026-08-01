.PHONY: up down build setup migrate seed fresh test queue cache-clear logs build-frontend

# Start all services
up:
	docker-compose up -d

# Stop all services
down:
	docker-compose down

# Build images (no cache)
build:
	docker-compose build --no-cache

# Full setup from scratch
setup: up
	docker-compose exec php composer install
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan storage:link
	docker-compose exec php php artisan migrate:fresh --seed
	docker-compose run --rm node
	@echo "✅ SIPDOK is ready at http://localhost:8080"

# Build frontend
build-frontend:
	docker-compose run --rm node

# Run migrations
migrate:
	docker-compose exec php php artisan migrate

# Run seeders
seed:
	docker-compose exec php php artisan db:seed

# Fresh migration + seed
fresh:
	docker-compose exec php php artisan migrate:fresh --seed

# Run tests
test:
	docker-compose exec php php artisan test

# Generate app key
key:
	docker-compose exec php php artisan key:generate

# Queue worker
queue:
	docker-compose exec php php artisan queue:work

# Clear all caches
cache-clear:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# View logs
logs:
	docker-compose logs -f
