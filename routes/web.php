<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RfidController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('prevent.back')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::post('search', [StudentController::class, 'search'])->name('search');
        Route::match(['post', 'get'], 'register', [StudentController::class, 'register'])->name('register.student.parent');
        Route::post('import', [StudentController::class, 'importCSV'])->name('importCSV');
        Route::get('logs', [RfidController::class, 'index'])->name('logs.index');
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');

        Route::get('report/filter', [AttendanceController::class, 'filterAttendance'])->name('attendances.filter');
        Route::get('report', [AttendanceController::class, 'reports'])->name('attendances.reports');
        
        Route::put('attendances/{student}', [AttendanceController::class, 'update'])->name('attendances.update');
        Route::resource('students', StudentController::class);


        Route::get('students/{student}', [StudentController::class, 'profile'])->name('student.profile');




        Route::resource('guardians', GuardianController::class);
        Route::get('notifications', [NotificationController::class,'index'])->name('notifications.index');
    });
    Route::match(['post', 'get'], 'verify', [RfidController::class, 'verify'])->name('verify');
    Route::match(['post', 'get'], 'login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

