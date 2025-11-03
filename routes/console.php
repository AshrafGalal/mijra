<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:delete-pending-invoices')->daily();
Schedule::command('verification-codes:cleanup')->daily();
Schedule::command('subscriptions:reminders')->daily();
Schedule::command('stories:cleanup')->hourly();
