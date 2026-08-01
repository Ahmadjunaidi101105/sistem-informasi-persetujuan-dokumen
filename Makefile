.PHONY: up down build setup migrate seed fresh test queue cache-clear logs build-frontend optimize restart-php

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
	$(MAKE) optimize
	@echo "✅ SIPDOK is ready at http://localhost:8080"

# Cache config + routes. The application source is bind-mounted, and file access
# across that mount is ~1000x slower than the container filesystem, so collapsing
# config/route loading into single cached files cuts request latency dramatically
# (measured: ~5.4s -> ~0.25s per API request).
# Safe to re-run; `make test` transparently drops the config cache so the suite
# always resolves its own (isolated) database from env.
optimize:
	docker-compose exec php php artisan config:cache
	docker-compose exec php php artisan route:cache
	@echo "✅ Config & routes cached"

# OPcache runs with validate_timestamps=0 for speed, so PHP edits need a reload.
restart-php:
	docker-compose restart php queue
	@echo "✅ PHP reloaded (OPcache reset)"

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

# Clear all caches (run `make optimize` afterwards to restore fast responses)
cache-clear:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# View logs
logs:
	docker-compose logs -f
