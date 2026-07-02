# Larapoll - Modern Polling Application

Larapoll is a modern, feature-rich polling application built with Laravel and Filament. It allows users to create polls, vote on them, and view real-time results with an intuitive admin dashboard.

## Features

### 🗳️ Poll Management
- Create polls with multiple options
- Set poll expiration dates (supports timezones!)
- Toggle poll activity status
- Automatic slug generation for shareable URLs
- User-scoped polls (each user manages their own polls)

### 🎯 Voting System
- IP-based vote tracking to prevent duplicate voting
- Real-time vote counting with Redis
- Queue-based vote persistence for high performance
- Throttled voting (5 votes per minute per IP)

### 📊 Admin Dashboard (Filament)
- Comprehensive poll management interface
- Real-time vote statistics and analytics widgets
- Vote activity tracking
- User and role management (powered by Spatie Laravel Permission)
- User-friendly CRUD operations for polls and options

### 🔒 Security Features
- IP address tracking for vote validation
- Rate limiting on voting endpoints
- Queue-based job processing for votes
- Database transactions for data integrity
- Role-based access control (RBAC)

## Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **Filament 5** - Admin panel
- **Redis** - In-memory data store (vote counting, cache, queues, sessions)
- **SQLite** - Default database (configurable to MySQL/PostgreSQL)
- **Spatie Laravel Permission** - Role and permission management

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
- Redis (required for full functionality)

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
   - Default: SQLite (no extra config needed, database file will be created at `database/database.sqlite`)
   - For MySQL/PostgreSQL, update `.env` accordingly:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=larapoll
     DB_USERNAME=root
     DB_PASSWORD=
     ```

6. **Set your timezone**
   Update `.env` to your local timezone (e.g., `Asia/Kolkata`, `America/New_York`, `UTC`):
   ```env
   APP_TIMEZONE=Asia/Kolkata
   ```

7. **Configure Redis**
   Ensure Redis is running, then update `.env`:
   ```env
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

8. **Configure Reverb (for real-time features)**
   Generate Reverb credentials:
   ```bash
   php artisan reverb:install
   ```
   This will auto-populate your `.env` with Reverb variables.

9. **Run migrations**
   ```bash
   php artisan migrate
   ```

10. **Build assets**
    ```bash
    npm run build
    ```

11. **Create an admin user**
    Use Tinker to create a super-admin:
    ```bash
    php artisan tinker
    ```
    ```php
    use App\Models\User;
    use Spatie\Permission\Models\Role;

    // Create roles (if not exists)
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    // Create super-admin user
    $user = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $user->assignRole('super_admin');
    ```

12. **Start the servers**
    - **Laravel development server**: `php artisan serve`
    - **Queue worker** (in a separate terminal): `php artisan queue:work redis`
    - **Reverb WebSocket server** (in another terminal): `php artisan reverb:start`

13. **Access the application**
    - Public site: http://localhost:8000
    - Admin panel: http://localhost:8000/admin
    - Log in with the admin credentials you created

## Development

### Running in Development Mode
```bash
npm run dev
```
This starts the Vite dev server with hot module replacement.

### Starting Reverb with Hot Reload
```bash
php artisan reverb:start --watch
```

### Running Tests
```bash
php artisan test
```

### Syncing Vote Counts
If Redis counts get out of sync with the database, run:
```bash
php artisan app:sync-vote-counts
```

## Project Structure

### Key Directories
- `app/Models/` - Eloquent models (Poll, PollOption, Vote, User)
- `app/Http/Controllers/` - Application controllers (PollController, VoteController)
- `app/Jobs/` - Queue jobs (SaveVote)
- `app/Filament/` - Admin panel resources, widgets, and pages
- `app/Services/` - Business logic services (VoteService, VoteCounter)
- `database/migrations/` - Database migrations
- `resources/views/` - Blade templates
- `resources/js/` - JavaScript files (poll.js, echo.js)
- `tests/` - PHPUnit tests

### Key Files
- `app/Jobs/SaveVote.php` - Queue job for persisting votes to the database
- `app/Services/VoteCounter.php` - Redis-based vote counting and voter tracking
- `app/Services/VoteService.php` - Core voting logic
- `app/Models/Poll.php` - Poll model with relationships and helper methods
- `routes/web.php` - Public routes
- `config/app.php` - Application configuration (including timezone)

## Usage

### Creating a Poll
1. Log in to the admin panel at `/admin`
2. Navigate to "Polls" in the sidebar
3. Click "Create Poll"
4. Enter the poll question and options
5. Set expiration date (optional)
6. Toggle poll active status (default: active)
7. Save the poll

### Voting
1. Visit the public poll page (e.g., `/polls/my-poll-question-random123`)
2. If the poll is open, select an option
3. Click "Vote"
4. View real-time results (updates automatically!)

### Viewing Results
- Public poll pages show live vote counts and percentages with a progress bar
- Admin dashboard has:
  - Vote Stats Overview widget (total polls, votes, open/closed counts)
  - Vote Activity Table (recent votes)
  - Individual poll results page

## Configuration

### Environment Variables
Key environment variables to configure (see `.env.example` for full list):

```env
APP_NAME=Larapoll
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Queue Configuration
For production, use Redis queues (already set as default in updated `.env.example`):
```env
QUEUE_CONNECTION=redis
```

Ensure you have queue workers running with [Supervisor](https://laravel.com/docs/queues#supervisor-configuration) for reliability.

## Deployment

### Production Considerations
1. **Use Redis** for queue, cache, and sessions (critical for performance)
2. **Configure proper database** (MySQL or PostgreSQL recommended instead of SQLite)
3. **Set up Supervisor** to manage queue workers and Reverb server
4. **Set environment variables** for production:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://your-domain.com`
5. **Use HTTPS** for all connections
6. **Set up automated backups** for the database
7. **Sync vote counts regularly** (add `app:sync-vote-counts` to your cron schedule)

### Deployment Steps
1. Clone the repository to your server
2. Install dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install --frozen-lockfile
   npm run build
   ```
3. Configure `.env` with production values
4. Run migrations:
   ```bash
   php artisan migrate --force
   ```
5. Set up Supervisor for:
   - Queue workers (`php artisan queue:work redis --sleep=3 --tries=3`)
   - Reverb server (`php artisan reverb:start`)
6. Configure your web server (Nginx/Apache)
7. Set up SSL/TLS certificate

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Write or update tests for new functionality
5. Ensure all tests pass (`php artisan test`)
6. Commit your changes (`git commit -m 'Add some amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and feature requests, please use the GitHub issue tracker.