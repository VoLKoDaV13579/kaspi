<?php

use App\Console\Commands\fetchPrices;
use App\Jobs\fetchPriceChange;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Ставит команды в планировщик задач.
Schedule::command(fetchPrices::class)->everyFiveMinutes();
