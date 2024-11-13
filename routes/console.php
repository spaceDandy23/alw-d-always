<?php


use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->everyFiveSeconds();


// Artisan::command('say:hello', function () {
//     $this->comment('Hello');
// })->purpose('Say hello');


// Schedule::command('app:daily-attendance')->daily();
// Schedule::command('app:message-parent')->dailyAt('12:00');
// Schedule::command('app:message-parent-lunch')->dailyAt('17:00');





