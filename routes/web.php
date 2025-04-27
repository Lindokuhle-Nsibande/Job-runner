<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::delete('/jobs/{id}', [JobController::class, 'destroy'])->name('jobs.destroy');
Route::get('/test-job', function () {
    runBackgroundJob(
        \App\Http\Controllers\Example::class,
        'handle',
        ["value1", "value2"]
    );
    return 'Job dispatched!';
});