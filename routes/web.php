<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RfidLogController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('prevent_back_history')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::post('import', [StudentController::class, 'importCSV'])->name('importCSV');
        Route::get('logs', [RfidLogController::class, 'index'])->name('logs.index');
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        // Route::resource('students', StudentController::class);
        Route::resource('guardians', GuardianController::class);
        Route::get('notifications', [NotificationController::class,'index'])->name('notifications.index');
    });

    Route::match(['post', 'get'], 'login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

