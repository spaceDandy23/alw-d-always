<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        
        $activeSchoolYear = SchoolYear::where('is_active', true)->first();
        $totalStudents = Student::where('school_year_id', $activeSchoolYear->id )
        ->count();
        $totalDaysRecorded = (Carbon::parse(Attendance::orderBy('date')
        ->whereHas('student', function($query) use ($activeSchoolYear){
            return $query->where('school_year_id', $activeSchoolYear->id);
        })
        ->first()->date))->diffInDays(today());

        $overallAverageAttendanceRate = Attendance::
        whereHas('student', function($query) use ($activeSchoolYear){
            return $query->where('school_year_id', $activeSchoolYear->id);
        })->select(DB::raw('
        (
            SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END)
        ) / 
        (
            SUM(CASE WHEN status_morning IN ("present", "absent") THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_lunch IN ("present", "absent") THEN 0.5 ELSE 0 END)
        ) AS overall_attendance_rate
        '))
        ->first();



        $attendancePerMonth = Attendance::select(DB::raw('
        MONTH(date) as month,
        YEAR(date) as year,
        SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) +
        SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END) AS total_present
        '))
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();


        $perfectAttendance = Attendance::select('student_id', DB::raw('
        COUNT(CASE WHEN status_morning = "absent" THEN 1 END) as total_morning,
        COUNT(CASE WHEN status_lunch = "absent" THEN 1 END) as total_lunch
        '))
        ->groupBy('student_id')
        ->having('total_morning', '=', 0) 
        ->having('total_lunch', '=', 0)   
        ->paginate(5);



        $absentAlot = Attendance::select('student_id', DB::raw('
            SUM(CASE WHEN status_morning = "absent" THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_lunch = "absent" THEN 0.5 ELSE 0 END) as total_absent
        '))
        ->having('total_absent', '>=', 5)
        ->groupBy('student_id')
        ->paginate(5);



        $overallAttendanceSummary = [
            'activeSchoolYear' => $activeSchoolYear->year,
            'totalStudents' => $totalStudents,
            'totalDaysRecorded' => $totalDaysRecorded,
            'overallAverageAttendanceRate' => $overallAverageAttendanceRate->overall_attendance_rate * 100
        ];


        $recentAttendanceRecords = Attendance::whereDate('date', today())->paginate(5);

        $attendanceBySection = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->select('students.grade', 'students.section', DB::raw('
            (
            SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END)
            )/(
            SUM(CASE WHEN status_lunch IN ("present", "absent") THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_morning IN ("present", "absent") THEN 0.5 ELSE 0 END)
            ) as section_overall
        
        '))
        ->where('students.school_year_id', $activeSchoolYear->id)
        ->groupBy('students.grade', 'students.section')
        ->get();


        $attendanceTrend = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->select('students.grade', 'students.section', DB::raw('
            YEAR(date) as year, 
            MONTH(date) as month,
            SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) + SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 1 END)
            AS total_present
        '))
        ->groupBy('students.grade', 'students.section', DB::raw('YEAR(date), MONTH(date)'))
        ->get();
        
        return view('admin_dashboard', compact('overallAttendanceSummary', 
        'attendancePerMonth', 'perfectAttendance', 'absentAlot', 'recentAttendanceRecords',
                    'attendanceBySection', 'attendanceTrend'));
    }




}
