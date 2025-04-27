#!/usr/bin/env php
<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Log;

// Enable error reporting for CLI
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    // Validate input
    if (count($argv) < 4) {
        throw new RuntimeException("Invalid arguments. Usage: php run-job.php ClassName methodName \"JSON_PARAMS\"");
    }

    $class = $argv[1];
    $method = $argv[2];
    $rawParams = $argv[3];
    $jobId = $argv[4] ?? null;

    echo "Attempting to run: $class@$method\n";

    // Validate class existence
    if (!class_exists($class)) {
        throw new RuntimeException("Class $class does not exist");
    }

    // Validate method existence
    if (!method_exists($class, $method)) {
        throw new RuntimeException("Method $method does not exist on $class");
    }

    // Parse parameters
    if (is_base64($rawParams)) {
        $rawParams = base64_decode($rawParams);
    }
    if (preg_match('/^\[.*\]$/', $rawParams)) {
        $parameters = json_decode($rawParams, true);
    } else {
        $parameters = explode(',', $rawParams);
    }
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON parameters: " . json_last_error_msg());
    }

    // Validate allowed jobs
    $allowedJobs = config('background-jobs.allowed');
    if (!isset($allowedJobs[$class]) || !in_array($method, $allowedJobs[$class])) {
        throw new RuntimeException("Job $class@$method is not allowed. Check config/background-jobs.php");
    }

    // Instantiate and execute
    $instance = app()->make($class);
    call_user_func_array([$instance, $method], $parameters);

    echo "Job executed successfully!\n";
    Log::channel('background_jobs')->info("Job succeeded", [
        'class' => $class,
        'method' => $method,
        'parameters' => $parameters,
    ]);

    exit(0);
} catch (Throwable $e) {
    $errorMessage = "ERROR: " . $e->getMessage();
    echo $errorMessage . "\n";

    Log::channel('background_jobs_errors')->error($errorMessage, [
        'class' => $class ?? 'unknown',
        'method' => $method ?? 'unknown',
        'trace' => $e->getTraceAsString(),
    ]);

    exit(1);
}
function is_base64($string)
{
    // Check if string is empty
    if (empty($string)) {
        return false;
    }

    // Check if decoded and encoded again equals original
    $decoded = base64_decode($string, true);
    if ($decoded === false) {
        return false;
    }

    return base64_encode($decoded) === $string;
}