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

        

        if(!$activeSchoolYear){
            return view('admin_dashboard', compact('activeSchoolYear'));
        }
        $totalStudents = Student::where('school_year_id', $activeSchoolYear->id )
        ->count();
        $totalDaysRecorded = Attendance::orderBy('date')
        ->whereHas('student', function($query) use ($activeSchoolYear){
            return $query->where('school_year_id', $activeSchoolYear->id);
        })
        ->first();

        if($totalDaysRecorded){
            $totalDaysRecorded = Carbon::parse($totalDaysRecorded->date)->diffInDays(today());
        }

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
        ->whereHas('student', function($q) use($activeSchoolYear){
            return $q->where('school_year_id', $activeSchoolYear->id);
        })
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();


        $perfectAttendance = Attendance::select('student_id', DB::raw('
        COUNT(CASE WHEN status_morning = "absent" THEN 1 END) as total_morning,
        COUNT(CASE WHEN status_lunch = "absent" THEN 1 END) as total_lunch
        '))
        ->whereHas('student', function($q) use($activeSchoolYear){
            return $q->where('school_year_id', $activeSchoolYear->id);
        })
        ->groupBy('student_id')
        ->having('total_morning', '=', 0) 
        ->having('total_lunch', '=', 0)   
        ->paginate(5);



        $absentAlot = Attendance::select('student_id', DB::raw('
            SUM(CASE WHEN status_morning = "absent" THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_lunch = "absent" THEN 0.5 ELSE 0 END) as total_absent
        '))
        ->whereHas('student', function($q) use($activeSchoolYear){
            return $q->where('school_year_id', $activeSchoolYear->id);
        })
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
            SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) + SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END)
            AS total_present
        '))
        ->where('students.school_year_id', $activeSchoolYear->id)
        ->groupBy('students.grade', 'students.section', DB::raw('YEAR(date), MONTH(date)'))
        ->get();


        $schoolYears = SchoolYear::all();
        
        return view('admin_dashboard', compact('overallAttendanceSummary', 
        'attendancePerMonth', 'perfectAttendance', 'absentAlot', 'recentAttendanceRecords',
                    'attendanceBySection', 'attendanceTrend', 'activeSchoolYear', 'schoolYears'));
    }
    public function backupDatabase()
    {
        $databaseName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD'); 
        $backupPath = storage_path('app/backups/backup_' . date('Y_m_d_His'));
    
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0777, true);
        }
    

        $command = "mysqldump -u $username";
        if (!empty($password)) {
            $command .= " -p$password";
        }
        $command .= " $databaseName > \"$backupPath\"";
    
        exec($command);
    
        return redirect()->route('dashboard')->with('success', 'Database successfully backed up');
    }

    public function changeSchoolYear(Request $request){

        $request->validate([
            'new_school_year' => 'required'
        ]);


        SchoolYear::where('is_active', true)->update(['is_active' => false]);

        SchoolYear::find($request->input('new_school_year'))->update(['is_active' => true]);


        
        return redirect()->route('dashboard')->with('success', 'School Year changed successfully');
    }

}
