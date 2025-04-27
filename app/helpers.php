<?php

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function runBackgroundJob(string $class, string $method, array $params = [], int $delay = 0, int $priority = 0): void
{
    // Validation
    $allowedJobs = config('background-jobs.allowed');
    if (!isset($allowedJobs[$class])) {
        Log::error("Job class not allowed", ['class' => $class]);
        throw new InvalidArgumentException("Job class $class is not allowed");
    }

    if (!in_array($method, $allowedJobs[$class])) {
        Log::error("Job method not allowed", ['class' => $class, 'method' => $method]);
        throw new InvalidArgumentException("Method $method not allowed for $class");
    }

    try {
        // Create job record
        $jobId = DB::table('background_jobs')->insertGetId([
            'class' => $class,
            'method' => $method,
            'parameters' => json_encode($params),
            'max_attempts' => config('background-jobs.retry.attempts', 3),
            'delay' => $delay,
            'priority' => $priority,
            'status' => 'pending',
            'available_at' => now()->addSeconds($delay),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Job queued", ['job_id' => $jobId]);

        // Build command
        $paramsString = base64_encode(json_encode($params));
        $command = [
            PHP_BINARY,
            base_path('run-job.php'),
            $class,
            $method,
            $paramsString,
            $jobId,
        ];

        // Execute if no delay
        if ($delay === 0) {
            $process = new Process($command);
            $process->setTimeout(null);

            $process->setWorkingDirectory(base_path());
            $process->start();

            Log::debug("Process started", [
                'command' => implode(' ', $command),
                'pid' => $process->getPid()
            ]);
        }
    } catch (Throwable $e) {
        Log::error("Failed to queue job", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}