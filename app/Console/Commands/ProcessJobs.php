<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class ProcessJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process background jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobs = DB::table('background_jobs')
            ->where('status', 'pending')
            ->where('available_at', '<=', now())
            ->orderByDesc('priority')
            ->orderBy('available_at')
            ->limit(5)
            ->get();

        foreach ($jobs as $job) {
            DB::table('background_jobs')
                ->where('id', $job->id)
                ->update(['status' => 'processing']);

            $command = [
                PHP_BINARY,
                base_path('run-job.php'),
                $job->class,
                $job->method,
                base64_encode($job->parameters),
                $job->id,
            ];

            $process = new Process($command);
            $process->setTimeout(null);
            $process->run();

            if ($process->isSuccessful()) {
                DB::table('background_jobs')
                    ->where('id', $job->id)
                    ->update(['status' => 'completed']);
            } else {
                $attempts = $job->attempts + 1;
                if ($attempts < $job->max_attempts) {
                    DB::table('background_jobs')
                        ->where('id', $job->id)
                        ->update([
                            'attempts' => $attempts,
                            'available_at' => now()->addSeconds($job->delay),
                            'status' => 'pending',
                        ]);
                } else {
                    DB::table('background_jobs')
                        ->where('id', $job->id)
                        ->update(['status' => 'failed']);
                }
            }
        }
    }
}