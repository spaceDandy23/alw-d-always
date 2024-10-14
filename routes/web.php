<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\RfidLogController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::post('import', [StudentController::class, 'importCSV'])->name('importCSV');

Route::match(['post','get'], 'login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('logs',[RfidLogController::class, 'index'])->name('logs.index');
Route::get('attendances',[AttendanceController::class, 'index'])->name('attendances.index');


Route::resource('students', StudentController::class);
Route::resource('guardians', GuardianController::class);