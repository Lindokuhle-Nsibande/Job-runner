# Custom Background Job Runner for Laravel

A platform-independent background job processing system for Laravel applications, designed to work without Laravel's built-in queue system.

## Features

-   Execute PHP classes/methods as background jobs
-   Secure job execution with allow-list configuration
-   Automatic retry mechanism for failed jobs
-   Web-based job monitoring dashboard
-   Delayed job execution
-   Job priority system
-   Detailed logging (success & errors)

## Installation

1. Clone repository:

```bash
git https://github.com/Lindokuhle-Nsibande/Job-runner
cd Job-runner

```

2. Install dependencies:

```bash
composer install

```

3. Create database and configure .env:

```.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=
```

4. Run migrations:

```bash
php artisan migrate

```

## Configuration

Edit config/background-jobs.php:

```php
return [
    'allowed' => [
        App\Http\Controllers\Example::class => ['handle'],
    ],
    'retry' => [
        'attempts' => 3,    // Max retry attempts
        'delay' => 60,      // Seconds between retries
    ],
];
```

## Usage

1. Using Helper Function

```php
runBackgroundJob(
    \App\Http\Controllers\Example::class,
    'handle',
    ['param1', 'param2'], // Parameters as array
    $delay = 60,          // Delay in seconds (optional)
    $priority = 10        // Higher = prioritized first (optional)
);
```

2. CLI Execution

```bash
php run-job.php "App\Http\Controllers\Example" "handle" ["param1","param2"]

```

3. Process Jobs

```bash
php artisan jobs:process

```

## Advanced Features

### Web Dashboard

Access job monitoring at: `http://localhost/jobs`

#### Features

-   View job status (pending/running/completed/failed)
-   See retry attempts
-   Cancel running jobs

### Job Delays

```php
// Run after 5 minutes
runBackgroundJob(Example::class, 'handle', [], 300);
```

### Job Priorities

```php
// High priority job (10)
runBackgroundJob(Example::class, 'handle', [], 0, 10);

// Normal priority (default 0)
runBackgroundJob(Example::class, 'handle', [], 0, 0);
```

### Logging

-   Success logs: `storage/logs/background_jobs.log`
-   Error logs: `storage/logs/background_jobs_errors.log`
