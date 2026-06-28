# Larapoll - Modern Polling Application

Larapoll is a modern, feature-rich polling application built with Laravel and Filament. It allows users to create polls, vote on them, and view real-time results with an intuitive admin dashboard.

## Features

### 🗳️ Poll Management
- Create polls with multiple options
- Set poll expiration dates
- Toggle poll activity status
- Automatic slug generation for shareable URLs

### 🎯 Voting System
- IP-based vote tracking to prevent duplicate voting
- Real-time vote counting
- Queue-based vote processing for better performance
- Throttled voting to prevent abuse

### 📊 Admin Dashboard (Filament)
- Comprehensive poll management interface
- Real-time vote statistics and analytics
- Vote activity tracking
- User-friendly CRUD operations for polls and options

### 🔒 Security Features
- IP address tracking for vote validation
- Rate limiting on voting endpoints
- Queue-based job processing for votes
- Database transactions for data integrity

## Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Filament 5** - Admin panel
- **Redis** - Cache and queue driver
- **MySQL** - Default database (configurable)

### Frontend
- **Bootstrap** - Styling framework
- **Alpine.js** - Interactive components
- **Vite** - Build tool

### Real-time Features
- **Laravel Reverb** - WebSocket server
- **Laravel Echo** - WebSocket client
- **Pusher.js** - WebSocket library

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- Redis (optional, for queue and cache)

### Step-by-Step Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd larapoll
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   - update `.env` to use MySQL/PostgreSQL:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=localhost
     DB_PORT=3306
     DB_DATABASE=larapoll
     DB_USERNAME=root
     DB_PASSWORD=123456
     ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

9. **Start queue worker** (in a separate terminal)
   ```bash
   php artisan queue:work
   ```
10. **Start queue worker** (in a separate terminal)
   ```bash
   php artisan queue:work
   ```

10. **Access the application**
    - Public site: http://localhost:8000
    - Admin panel: http://localhost:8000/admin
    - Register a new admin user at the admin login page

## Development

### Running in Development Mode
```bash
npm run dev
```

This command starts:
- Laravel development server
- Queue worker
- Log tailing
- Vite dev server

### Starting Reverb WebSocket Server
```bash
php artisan reverb:start
```

For development with hot reload:
```bash
php artisan reverb:start --watch
```

### Database Seeding
```bash
php artisan db:seed
```

### Running Tests
```bash
php artisan test
```

## Project Structure

### Key Directories
- `app/Models/` - Eloquent models (Poll, PollOption, Vote, User)
- `app/Http/Controllers/` - Application controllers
- `app/Jobs/` - Queue jobs (SaveVote)
- `app/Filament/` - Admin panel resources and widgets
- `app/Services/` - Business logic services
- `database/migrations/` - Database migrations
- `resources/views/` - Blade templates
- `tests/` - PHPUnit tests

### Key Files
- `app/Jobs/SaveVote.php` - Queue job for processing votes
- `app/Models/Poll.php` - Poll model with relationships
- `app/Filament/Resources/PollResource.php` - Admin panel resource
- `routes/web.php` - Public routes
- `config/filament.php` - Filament configuration

## Usage

### Creating a Poll
1. Log in to the admin panel at `/admin`
2. Navigate to "Polls" in the sidebar
3. Click "Create Poll"
4. Enter the poll question and options
5. Set expiration date if needed
6. Save the poll

### Voting
1. Visit the public poll page (e.g., `/polls/my-poll-question-random123`)
2. Select an option
3. Click "Vote"
4. View real-time results

### Viewing Results
- Public poll pages show live vote counts
- Admin dashboard provides detailed analytics
- Vote statistics widget shows overview
- Vote activity table shows recent votes

## Configuration

### Environment Variables
Key environment variables to configure:

```env
APP_NAME=Larapoll
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=larapoll
# DB_USERNAME=root
# DB_PASSWORD=

QUEUE_CONNECTION=database
# QUEUE_CONNECTION=redis

SESSION_DRIVER=database
CACHE_STORE=database
```

### Queue Configuration
The application uses database queues by default. For production, consider using Redis:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Testing

The application includes both feature and unit tests:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/VoteTest.php

# Run with coverage
php artisan test --coverage
```

## Deployment

### Production Considerations
1. **Use Redis** for queue and cache
2. **Configure proper database** (MySQL/PostgreSQL)
3. **Set up supervisor** for queue workers
4. **Configure environment variables** for production
5. **Use HTTPS** for secure connections
6. **Set up backup** for database

### Deployment Steps
1. Clone the repository to your server
2. Install dependencies: `composer install --no-dev`
3. Build assets: `npm run build`
4. Run migrations: `php artisan migrate --force`
5. Set up queue workers with supervisor
6. Set up Reverb WebSocket server with supervisor
7. Configure web server (Nginx/Apache)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and feature requests, please use the GitHub issue tracker.