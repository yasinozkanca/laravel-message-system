# Message Sending System

Laravel-based bulk message sending system with queue processing and webhook integration.

## Features

- Bulk message sending with rate limiting
- Queue-based background processing
- Redis caching
- RESTful API
- Swagger documentation
- Webhook integration

## Requirements

- PHP 8.2+
- Composer
- MySQL 5.7+
- Redis
- Laravel 10.x

## Installation

```bash
git clone <repository-url>
cd insider-messaging-system
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan l5-swagger:generate
```

## Configuration

Update `.env`:

```env

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=messaging_system
DB_USERNAME=messaging_user
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=predis

WEBHOOK_URL=https://webhook.site/0102047c-2680-4c44-9a0f-f9d947053257
WEBHOOK_AUTH_KEY=INS.me1x9uMcyYG1hKKQVPoc.b03j9aZwRTOCA2Ywo
```

## Usage

First, build the docker then follow the bash commands.

## Docker

```bash
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan migrate
docker-compose exec app php artisan key:generate

docker-compose exec app php artisan messages:send # for manually triggering messaging service
```

API endpoints:
- `GET /api/messages` - List sent messages
- `POST /api/messages` - Create message

Swagger docs: `http://localhost:8000/api/documentation`

## Testing

```bash
php artisan test
```

