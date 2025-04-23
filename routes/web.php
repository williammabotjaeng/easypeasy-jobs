<?php

use App\Http\Controllers\JobDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard.jobs');
});

Route::get('/dashboard/jobs', [JobDashboardController::class, 'index'])->name('dashboard.jobs');
Route::post('/dashboard/jobs/cancel', [JobDashboardController::class, 'cancel'])->name('dashboard.jobs.cancel');
