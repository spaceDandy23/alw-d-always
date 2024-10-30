<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RfidController;
use App\Http\Controllers\SpecialOccasionController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;



Route::middleware('prevent.back')->group(function () {
    Route::middleware(['auth'])->group(function () {
        Route::post('search', [StudentController::class, 'search'])->name('search');
        Route::match(['post', 'get'], 'register', [StudentController::class, 'register'])->name('register.student.parent');
        Route::post('import', [StudentController::class, 'importCSV'])->name('importCSV');

        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        Route::get('logs/filter', [RfidController::class, 'search'])->name('logs.filter');
        Route::get('logs', [RfidController::class, 'index'])->name('logs.index');
        
        

        Route::get('attendances/filter', [AttendanceController::class, 'search'])->name('attendances.reports.filter');
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');


        

        Route::resource('holidays', HolidayController::class);


        Route::put('attendances/{student}', [AttendanceController::class, 'update'])->name('attendances.update');

        Route::get('students/filter', [StudentController::class, 'search'])->name('students.filter');
        Route::resource('students', StudentController::class);

        Route::get('student/{student}', [StudentController::class, 'profile'])->name('student.profile');
        Route::get('student/{student}/filter', [StudentController::class,'filterStudentAttendance'])->name('student.filter');


        Route::get('guardians/filter', [GuardianController::class, 'search'])->name('guardians.filter');
        Route::resource('guardians', GuardianController::class);

        Route::get('notifications', [NotificationController::class,'index'])->name('notifications.index');
    });
    Route::match(['post', 'get'], 'verify', [RfidController::class, 'verify'])->name('verify');
    Route::match(['post', 'get'], 'login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

