# Insider Messaging System

A Laravel-based message sending system with Redis queue processing and webhook integration.

## Features

- RESTful API with Swagger documentation
- Redis caching and queue processing
- Docker containerization
- Rate-limited message sending (2 messages per 5 seconds)
- Background job processing

## Requirements

- Docker
- Docker Compose

## Setup

1. **Clone the repository**
```bash
git clone git@github.com:yasinozkanca/laravel-message-system.git
cd insider-messaging-system
```

2. **Build and start containers**
```bash
docker-compose build
docker-compose up -d
```

3. **Initialize application**
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh --seed # For generating messages
docker-compose exec app php artisan l5-swagger:generate
```

4. **Start queue worker**
```bash
docker-compose up -d queue
```

## Usage

**Access the application:**
- API: `http://localhost:8000`
- Swagger Documentation: `http://localhost:8000/api/documentation`

**API Endpoints:**
- `GET /api/messages` - List sent messages
- `POST /api/messages` - Create new message

**Test the API:**
```bash
curl -X POST http://localhost:8000/api/messages \
  -H "Content-Type: application/json" \
  -d '{"content": "Test message", "phone_number": "+1234567890"}'
```

# Manual message processing
```bash
docker-compose exec app php artisan messages:send
```

## Testing


```bash
docker-compose exec app php artisan test
```

## Common Commands

```bash
# View logs
docker-compose logs app

# Stop services
docker-compose down

# Restart services
docker-compose restart app
```


## Architecture

- **App Container**: Laravel application
- **Nginx Container**: Web server
- **Database Container**: MySQL 8.0
- **Redis Container**: Cache and queue
- **Queue Container**: Background job processor

