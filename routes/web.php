<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RfidController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::middleware('prevent.back')->group(function () {
    Route::middleware(['admin'])->group(function () {
        Route::post('search', [StudentController::class, 'search'])->name('search');
        Route::match(['post', 'get'], 'register', [StudentController::class, 'register'])->name('register.student.parent');
        Route::post('import', [StudentController::class, 'importCSV'])->name('importCSV');

        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        Route::post('/undo/{id}', [AdminController::class, 'undoImport'])->name('import.undo');
        Route::post('restore', [AdminController::class, 'restoreDatabase'])->name('restore.database');
        
        Route::get('logs/filter', [RfidController::class, 'search'])->name('logs.filter');
        Route::get('logs', [RfidController::class, 'index'])->name('logs.index');

        Route::get('attendances/filter', [AttendanceController::class, 'search'])->name('attendances.reports.filter');
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        

        Route::post('message/parents', [NotificationController::class,'massMessage'])->name('mass.message.parent');
        Route::post('message/parent/{student}', [NotificationController::class, 'messageParent'])->name('message.parent');
        Route::post('message/edit', [NotificationController::class, 'editMessage'])->name('change.message');
        Route::match(['post', 'get'],'review/edit', [AttendanceController::class, 'editAttendance'])->name('edit.attendance');
        Route::resource('users', UserController::class);
        

        Route::resource('holidays', HolidayController::class);


        Route::put('attendances/{student}', [AttendanceController::class, 'update'])->name('attendances.update');

        Route::get('students/filter', [StudentController::class, 'search'])->name('students.filter');
        Route::resource('students', StudentController::class);

        
        Route::get('student/{student}', [StudentController::class, 'profile'])->name('student.profile');
        Route::get('student/{student}/filter', [StudentController::class,'filterStudentAttendance'])->name('student.filter');
        Route::post('archive', [AdminController::class, 'backupDatabase'])->name('back.up');

        Route::post('change', [AdminController::class, 'changeSchoolYear'])->name('change.school.year');


        Route::get('guardians/filter', [GuardianController::class, 'search'])->name('guardians.filter');
        Route::resource('guardians', GuardianController::class);
        Route::get('notifications/filter', [NotificationController::class, 'search'])->name('notifications.filter');
        Route::get('notifications', [NotificationController::class,'index'])->name('notifications.index');
        Route::get('excuse', [AttendanceController::class, 'attendances'])->name('excuse.index');
        Route::post('excuse', [AttendanceController::class,'excuseApply'])->name('excuse.apply');
        Route::post('cancel', [AttendanceController::class, 'cancelClassSession'])->name('cancel.attendance.session');
        Route::get('review', [AttendanceController::class, 'reviewAttendance'])->name('review.attendance.index');
    });
    Route::match(['post', 'get'], 'verify', [RfidController::class, 'verify'])->name('verify');
    Route::match(['post', 'get'], 'login', [AuthController::class, 'login'])->name('login')->middleware('guest');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['teacher'])->group(function (){

        Route::get('logs/filter', [RfidController::class, 'search'])->name('logs.filter');
        Route::get('logs', [RfidController::class, 'index'])->name('logs.index');

        Route::get('attendances/filter', [AttendanceController::class, 'search'])->name('attendances.reports.filter');
        Route::get('attendances/filter/export', [AttendanceController::class, 'exportReport'])->name('export.report');
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::post('set', [AttendanceController::class, 'setDay'])->name('set.day');
        Route::post('mark', [TeacherController::class, 'markAttendance'])->name('mark.attendance');
        Route::get('dashboard', [TeacherController::class, 'index'])->name('teacher.dashboard');
        Route::get('class/attendance', [TeacherController::class, 'classAttendance'])->name('class.attendance.index');
        Route::get('class/attendance/filter', [TeacherController::class, 'search'])->name('class.filter');
        
        Route::get('class', [TeacherController::class, 'classIndex'])->name('class.index');
        Route::put('class/{student}', [TeacherController::class,'updateClassAttendance'])->name('class.attendance.update');
        Route::post('class/store', [TeacherController::class, 'storeClass'])->name('create.class');
        Route::post('class/unenroll/students', [TeacherController::class, 'unenrollStudent'])->name('unenroll.students');
        Route::post('class/remove', [TeacherController::class, 'removeClass'])->name('class.delete');
        Route::post('class/add', [TeacherController::class, 'addStudent'])->name('class.add.student');
    });
});

