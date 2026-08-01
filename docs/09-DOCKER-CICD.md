# Docker & CI/CD — SIPDOK

## 1. Docker Architecture

```
┌─────────────────────────────────────────────────┐
│                Docker Network: sipdok            │
│                                                  │
│  ┌─────────┐  ┌──────────┐  ┌───────────────┐  │
│  │  Nginx  │  │ PHP-FPM  │  │  PostgreSQL   │  │
│  │  :80    │──│  :9000   │──│  :5432        │  │
│  └─────────┘  └──────────┘  └───────────────┘  │
│       │                            │             │
│  ┌─────────┐                 ┌───────────┐      │
│  │  Node   │                 │   Redis   │      │
│  │ (build) │                 │   :6379   │      │
│  └─────────┘                 └───────────┘      │
│                                                  │
└──────────────────────────────────────────────────┘
```

## 2. Docker Compose Services

### docker-compose.yml
```yaml
version: '3.8'

services:
  # Nginx Web Server
  nginx:
    image: nginx:1.25-alpine
    ports:
      - "8080:80"
    volumes:
      - ./backend:/var/www/html
      - ./frontend/dist:/var/www/html/public/frontend
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
    networks:
      - sipdok

  # PHP-FPM
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - ./backend:/var/www/html
    depends_on:
      - postgres
      - redis
    environment:
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=sipdok
      - DB_USERNAME=sipdok
      - DB_PASSWORD=sipdok_secret
      - REDIS_HOST=redis
      - CACHE_DRIVER=redis
      - QUEUE_CONNECTION=redis
      - SESSION_DRIVER=redis
    networks:
      - sipdok

  # Queue Worker
  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    volumes:
      - ./backend:/var/www/html
    depends_on:
      - php
    environment:
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=sipdok
      - DB_USERNAME=sipdok
      - DB_PASSWORD=sipdok_secret
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
    networks:
      - sipdok

  # PostgreSQL
  postgres:
    image: postgres:16-alpine
    ports:
      - "5433:5432"
    environment:
      POSTGRES_DB: sipdok
      POSTGRES_USER: sipdok
      POSTGRES_PASSWORD: sipdok_secret
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - sipdok

  # Redis
  redis:
    image: redis:7-alpine
    ports:
      - "6380:6379"
    volumes:
      - redis_data:/data
    networks:
      - sipdok

  # Node (Frontend Build)
  node:
    image: node:20-alpine
    working_dir: /app
    volumes:
      - ./frontend:/app
    command: sh -c "npm install && npm run build"
    profiles:
      - build

volumes:
  postgres_data:
  redis_data:

networks:
  sipdok:
    driver: bridge
```

## 3. Dockerfiles

### docker/php/Dockerfile
```dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        gd \
        bcmath \
        pcntl

# Install Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY backend/ .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### docker/nginx/default.conf
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php;

    # Frontend SPA (built files)
    location /app {
        alias /var/www/html/public/frontend;
        try_files $uri $uri/ /frontend/index.html;
    }

    # API routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Sanctum CSRF
    location /sanctum {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 4. Quick Start Commands (Makefile)

```makefile
.PHONY: up down build migrate seed fresh test

# Start all services
up:
	docker-compose up -d

# Stop all services
down:
	docker-compose down

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

# Clear all caches
cache-clear:
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# Full setup from scratch
setup: up
	docker-compose exec php composer install
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan migrate:fresh --seed
	docker-compose exec php php artisan storage:link
	docker-compose run --rm node
	@echo "✅ SIPDOK is ready at http://localhost:8080"

# Queue worker
queue:
	docker-compose exec php php artisan queue:work

# Logs
logs:
	docker-compose logs -f
```

## 5. GitLab CI/CD

### .gitlab-ci.yml
```yaml
stages:
  - lint
  - test
  - build

variables:
  POSTGRES_DB: sipdok_testing
  POSTGRES_USER: sipdok
  POSTGRES_PASSWORD: sipdok_secret
  DB_CONNECTION: pgsql
  DB_HOST: postgres
  DB_DATABASE: sipdok_testing
  DB_USERNAME: sipdok
  DB_PASSWORD: sipdok_secret
  CACHE_DRIVER: array
  QUEUE_CONNECTION: sync

# Lint PHP (PSR-12)
lint-php:
  stage: lint
  image: php:8.3-cli-alpine
  before_script:
    - apk add --no-cache postgresql-dev
    - docker-php-ext-install pdo pdo_pgsql
    - cd backend
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install --no-progress
  script:
    - ./vendor/bin/php-cs-fixer fix --dry-run --diff
  allow_failure: true

# Lint Frontend (ESLint)
lint-frontend:
  stage: lint
  image: node:20-alpine
  before_script:
    - cd frontend
    - npm ci
  script:
    - npm run lint
  allow_failure: true

# Backend Tests
test-backend:
  stage: test
  image: php:8.3-cli-alpine
  services:
    - postgres:16-alpine
  before_script:
    - apk add --no-cache postgresql-dev
    - docker-php-ext-install pdo pdo_pgsql bcmath
    - cd backend
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install --no-progress
    - cp .env.testing .env
    - php artisan key:generate
    - php artisan migrate
  script:
    - php artisan test --parallel
  artifacts:
    reports:
      junit: backend/storage/test-results/junit.xml

# Build Frontend
build-frontend:
  stage: build
  image: node:20-alpine
  before_script:
    - cd frontend
    - npm ci
  script:
    - npm run build
  artifacts:
    paths:
      - frontend/dist/
    expire_in: 1 week
```

## 6. Environment Files

### backend/.env.example
```env
APP_NAME=SIPDOK
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=sipdok
DB_USERNAME=sipdok
DB_PASSWORD=sipdok_secret

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:8080

FILESYSTEM_DISK=local

MAIL_MAILER=log
```

### backend/.env.testing
```env
APP_ENV=testing
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=sipdok_testing
DB_USERNAME=sipdok
DB_PASSWORD=sipdok_secret
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```
