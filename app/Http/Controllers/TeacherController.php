<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use Auth;
use DB;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(){


        $activeSchoolYear = SchoolYear::latest()->first()->id ?? '';
        $studentIds = Auth::user()->students()->pluck('id');

        $recentAttendanceRecords = Attendance::whereDate('date', today())->paginate(5);
        
        $perfectAttendance = Attendance::select('student_id', DB::raw('
        COUNT(CASE WHEN status_morning = "absent" THEN 1 END) as total_morning,
        COUNT(CASE WHEN status_lunch = "absent" THEN 1 END) as total_lunch
        '))
        ->whereHas('student', function($q) use($activeSchoolYear){
            return $q->where('school_year_id', $activeSchoolYear);
        })
        ->whereIn('attendances.student_id', $studentIds)
        ->groupBy('student_id')
        ->having('total_morning', '=', 0) 
        ->having('total_lunch', '=', 0)   
        ->paginate(5);

        



        $absentAlot = Attendance::select('student_id', DB::raw('
            SUM(CASE WHEN status_morning = "absent" THEN 0.5 ELSE 0 END) +
            SUM(CASE WHEN status_lunch = "absent" THEN 0.5 ELSE 0 END) as total_absent
        '))
        ->whereHas('student', function($q) use($activeSchoolYear){
            return $q->where('school_year_id', $activeSchoolYear);
        })
        ->whereIn('attendances.student_id', $studentIds)
        ->having('total_absent', '>=', 5)
        ->groupBy('student_id')
        ->paginate(5);



        $attendanceTrend = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->select('students.grade', 'students.section', DB::raw('
            YEAR(date) as year, 
            MONTH(date) as month,
            SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) + SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END)
            AS total_present
        '))
        ->where('students.school_year_id', $activeSchoolYear)
        ->whereIn('attendances.student_id', $studentIds)
        ->groupBy('students.grade', 'students.section', DB::raw('YEAR(date), MONTH(date)'))
        ->get();




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
        ->whereIn('attendances.student_id', $studentIds)
        ->where('students.school_year_id', $activeSchoolYear)
        ->groupBy('students.grade', 'students.section')
        ->get();



        return view('teacher_dashboard',compact('activeSchoolYear', 
        'recentAttendanceRecords', 'absentAlot', 'attendanceTrend', 'attendanceBySection', 'perfectAttendance'));

    }
    public function listIndex(){


        $studentsGroupedBySection = Auth::user()->students->groupBy(function ($student) {
            return $student->grade . '-' . $student->section;
        })
        ->map(function($students){

            return $students->each(function ($student){


                $attendanceCount = $student->attendances->count();
                $presentCount = $student->attendances()
                ->selectRaw('SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) 
                            + SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END) as present_count')
                ->value('present_count');

                $absentCount = $student->attendances()
                ->selectRaw('SUM(CASE WHEN status_morning = "absent" THEN 0.5 ELSE 0 END) 
                            + SUM(CASE WHEN status_lunch = "absent" THEN 0.5 ELSE 0 END) as absent_count')
                ->value('absent_count');


                $averagePresent = $attendanceCount > 0 ? round(($presentCount / $attendanceCount), 3) * 100 : 0;


                $averageAbsent = $attendanceCount > 0 ? round(($absentCount / $attendanceCount), 3) * 100 : 0;

                $student->recent_attendance = $student->attendances()
                ->where('date', now()->format('Y-m-d'))
                ->first();
                $student->recent_logs = $student->rfidLogs()
                ->where('date', now()->format('Y-m-d'))
                ->get();

                $student->average_present = $averagePresent;
                $student->average_absent = $averageAbsent;


                
            });
            

        });


        return view('students.watch_list', compact('studentsGroupedBySection'));
    }
    
    public function storeWatchlist(Request $request){
        $sections = $request->input('sections', []);

        if(!$sections){
            return back()->with('error', 'Please fill out at least one checkbox');
        }

        foreach($sections as $section){

            $students = Student::where('grade', $section[0])
            ->where('section', $section[2])
            ->where('school_year_id', SchoolYear::latest()->first()->id)
            ->pluck('id');
            if ($students->isNotEmpty()) {
                foreach ($students as $id) {
                    if (!Auth::user()->students()->where('student_id', $id)->exists()) {
                        Auth::user()->students()->attach($id, [
                            'created_at' => now(), 
                            'updated_at' => now()
                        ]);
                    }
                }

            }

        }
        
        
        return redirect()->route('list.index')->with('success','Section added successfully');


    }

    public function removeList(Request $request){

        $user = Auth::user();
        $studentIds = $user->students()
        ->where('grade', $request->section[0])
        ->where('section', $request->section[2])
        ->pluck('id');

        $user->students()->detach($studentIds);


        return redirect()->route('list.index')->with('success', 'Watchlist deleted');

    }
}
