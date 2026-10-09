<?php

use App\Models\OtpVerification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Production Scheduler Tasks
Schedule::call(function () {
    OtpVerification::where('created_at', '<', now()->subDays(2))->delete();
})->daily()->name('prune:expired-otps');

Schedule::command('queue:prune-failed --hours=168')->weekly()->name('prune:failed-jobs');
Schedule::command('auth:clear-resets')->daily()->name('auth:clear-resets');
