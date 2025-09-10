# HyperSenderTest

A Laravel-based transportation management demo that showcases a Filament Admin Panel with KPIs, availability management, and seeded sample data. The project includes widgets and pages to monitor drivers, vehicles, and trips in real time.

## Features
- Filament Admin Panel with a dashboard
  - KPI Overview widget showing:
    - Active trips now
    - Available drivers and vehicles with availability percentage
    - Completed trips this month with trend
    - Total resources (drivers, vehicles) and total trips
- Manager Availability page to review current availability (Filament page)
- Eloquent models for Companies, Drivers, Vehicles, Trips
- Database factories and seeders for realistic demo data
- Caching for dashboard KPIs to reduce DB load

## Tech Stack
- PHP (Laravel Framework)
- Filament Admin
- MySQL/MariaDB (or any DB supported by Laravel)
- Composer, Node.js (for asset build if needed)

## Prerequisites
- PHP 8.1+
- Composer 2+
- MySQL/MariaDB (or compatible DB)
- Node.js 18+ (optional; for building assets)

## Getting Started
1. Clone the repository
   - git clone <your-repo-url>
   - cd HyperSenderTest
2. Install dependencies
   - composer install
   - npm install (optional)
3. Environment setup
   - Copy .env.example to .env
   - Set DB_ credentials (host, database, username, password)
   - Optionally set APP_URL (e.g., http://localhost)
4. Generate app key
   - php artisan key:generate
5. Migrate and seed
   - php artisan migrate --seed
6. Storage symlink (for media, if needed)
   - php artisan storage:link
7. Serve the application
   - php artisan serve
   - Visit http://127.0.0.1:8000 (or your local domain)

## Accessing Filament Admin
- Default Filament URL: /admin
- If no default admin user is created, create one:
  - php artisan make:filament-user
  - Follow the prompts to set email and password

## Configuration Notes
- Caching: KPIs are cached for 60 seconds in app/Filament/Widgets/KpiOverview.php
- Timezone/locale: Configure in config/app.php and .env (APP_LOCALE, APP_TIMEZONE)

## Running Tests
- php artisan test
- Or vendor\bin\phpunit

## Project Highlights
- app\Filament\Widgets\KpiOverview.php — dashboard KPIs and charts
- app\Filament\Pages\ManagerAvailability.php — availability page
- app\Providers\Filament\AdminPanelProvider.php — Filament panel configuration
- database\factories\*.php — factories for seed data
- database\seeders\DatabaseSeeder.php — demo data seeding

## Common Artisan Commands
- php artisan migrate:fresh --seed — reset and reseed database
- php artisan cache:clear && php artisan config:clear — clear caches
- php artisan optimize — optimize framework caches

## Troubleshooting
- Filament assets not loading: run php artisan optimize:clear and ensure APP_URL is correct

## License
This project is open-sourced software licensed under the MIT license.

## Acknowledgements
- Laravel Framework — https://laravel.com
- Filament Admin — https://filamentphp.com
