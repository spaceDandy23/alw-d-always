<?php

use App\Models\Attendance;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->everyFiveSeconds();


Artisan::command('say:hello', function () {
    $this->comment('Hello');
})->purpose('Say hello');


Schedule::command('app:store-morning-attendance')->everyFiveSeconds()->when(function (){
    return now()->format('H') >= 12;
});
Schedule::command('app:store-afternoon-attendance')->everyFifteenSeconds()->when(function (){
    return now()->format('H') >= 5;
});