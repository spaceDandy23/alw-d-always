<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Guardian;
use App\Models\ImportBatch;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        


        $activeSchoolYear = SchoolYear::where('is_active', true)->first();

        

        if(!$activeSchoolYear){
            return view('admin.admin_dashboard', compact('activeSchoolYear'));
        }
        $totalStudents = Student::where('school_year_id', $activeSchoolYear->id )
        ->count();

        $totalDaysRecorded = Attendance::whereHas('student', function($query) use ($activeSchoolYear) {
            return $query->where('school_year_id', $activeSchoolYear->id);
        })
        ->count();


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


        // $perfectAttendance = Attendance::select('student_id', DB::raw('
        // COUNT(CASE WHEN status_morning = "absent" THEN 1 END) as total_morning,
        // COUNT(CASE WHEN status_lunch = "absent" THEN 1 END) as total_lunch
        // '))
        // ->whereHas('student', function($q) use($activeSchoolYear){
        //     return $q->where('school_year_id', $activeSchoolYear->id);
        // })
        // ->groupBy('student_id')
        // ->having('total_morning', '=', 0) 
        // ->having('total_lunch', '=', 0)   
        // ->limit(5)
        // ->get();



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
        ->join('sections', 'students.section_id', '=', 'sections.id') 
        ->select(DB::raw('
            CONCAT(sections.grade, " - ", sections.section) as section_name, 
            (
                SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END) +
                SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END)
            ) / (
                SUM(CASE WHEN status_lunch IN ("present", "absent") THEN 0.5 ELSE 0 END) +
                SUM(CASE WHEN status_morning IN ("present", "absent") THEN 0.5 ELSE 0 END)
            ) AS section_overall
        '))
        ->where('students.school_year_id', $activeSchoolYear->id)
        ->groupBy('sections.id','sections.grade', 'sections.section') 
        ->get();

        $attendanceTrend = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->join('sections', 'students.section_id', '=', 'sections.id')
        ->select(
            DB::raw('
                CONCAT(sections.grade, " - ", sections.section) as section_name, 
                YEAR(date) as year, 
                MONTH(date) as month,
                SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) + 
                SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END) AS total_present
            ')
        )
        ->where('students.school_year_id', $activeSchoolYear->id)
        ->groupBy('sections.grade', 'sections.section', DB::raw('YEAR(date), MONTH(date)')) 
        ->get();



        $schoolYears = SchoolYear::all();
        
        return view('admin.admin_dashboard', compact('overallAttendanceSummary', 'attendanceTrend',
        'attendancePerMonth',  'absentAlot', 'recentAttendanceRecords',
                    'attendanceBySection', 'activeSchoolYear', 'schoolYears'));
    }
    public function backupDatabase()
    {
        $databaseName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD'); 
        $backupPath = storage_path('app/backups/backup_' . date('Y_m_d_His') . '.sql');
    
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
    public function undoImport($importBatchId)
    {

    $importBatch = ImportBatch::findOrFail($importBatchId);
    Student::where('import_batch_id', $importBatchId)->delete();
    Guardian::where('import_batch_id', $importBatchId)->delete();
    Section::where('import_batch_id', $importBatchId)->delete();

    $importBatch->delete();


    
    $schoolYear = SchoolYear::latest()->first();
    if($schoolYear){
        $schoolYear->update(['is_active' => true]);
    }


    return redirect()->route('students.index')->with('success', 'Import undone successfully');
    }

}
