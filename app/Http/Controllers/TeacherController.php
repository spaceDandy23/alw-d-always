<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSectionTeacher;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Session;

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
        ->join('sections', 'students.section_id', '=', 'sections.id')
        ->select(DB::raw('
            CONCAT(sections.grade, " - ", sections.section) as section_name, 
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



        return view('teachers.teacher_dashboard',compact('activeSchoolYear', 
        'recentAttendanceRecords', 'absentAlot', 'attendanceTrend', 'attendanceBySection', 'perfectAttendance'));

    }
    public function classIndex(){

        $studentsAuth = Auth::user()->students()->get();
        $groupedBySection = $studentsAuth->groupBy(function($student) {
            return "{$student->section->grade}-{$student->section->section}"; 
        });
        $sections = Section::all();

        return view('teachers.section_teacher_list', compact('sections', 'groupedBySection'));
    }
    
    public function storeClass(Request $request){
        $sections = $request->input('sections', []);


        if(!$sections){
            return back()->with('error', 'Please fill out at least one checkbox');
        }

        $student = Student::whereIn('section_id', $sections)->get()->pluck('id');


        Auth::user()->students()->syncWithoutDetaching($student);   
        return redirect()->route('class.index')->with('success','Section added successfully');


    }

    public function removeClass(Request $request){

        $studentIds = Student::whereHas('section', function($q) use($request){
            return $q->where('grade', $request->section_id[0])
                    ->where('section', $request->section_id[2]);
        })
        ->get()
        ->pluck('id');

        Auth::user()->students()->detach($studentIds);

        return redirect()->route('class.index')->with('success', 'Watchlist deleted');

    }

    public function unenrollStudent(Request $request){



        if(!$request->students){
            return redirect()->route('class.index')->with('error', 'Fill in at least one checkbox');

        }
        


        foreach ($request->students as $id => $toCheck) {
            $authStudent = Auth::user()->students()
            ->where('student_id', $id);


            if(count($toCheck) > 1){
                $authStudent->updateExistingPivot($id, ['enrolled' => false]);
            }
            else{

                $authStudent->updateExistingPivot($id, ['enrolled' => true]);

            }
            
        }

        return redirect()->route('class.index')->with('success', 'Class updated');
    }

    public function markAttendance(){


        $sectionId = Session::get('section_id');

        $studentIds = Auth::user()
        ->sectionAttendances()
        ->where('date', now()->format('Y-m-d'))
        ->where('section_id', $sectionId)
        ->where('present', true)
        ->pluck('student_id');



        
        $absentStudentIds = Auth::user()->students()
        ->whereNotIn('student_id', $studentIds)
        ->where('enrolled', true)
        ->where('section_id', $sectionId)
        ->pluck('student_id');


        foreach ($absentStudentIds as $id){


            AttendanceSectionTeacher::firstOrCreate([
                'student_id' => $id,
                'teacher_id' => Auth::id(),
                'section_id' => $sectionId,
                'date' => today(),
                ],
                [
                    
                'time' => now()->format('H:i:s'),
                'present' => false
                
                ]
            );
        }



        return redirect()->back()->with('success', 'Attendance Marked');
        
    }
    public function classAttendance(){

        $classAttendances = Auth::user()->attendanceStudents()->paginate(30);
        return view('teachers.class_attendance', compact('classAttendances'));



    }
    public function search(Request $request){


        $request->validate([        
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ], [
            'end_time.after_or_equal' => 'The end time must be a time after or equal to the start time.',
        ]);
    
        $name = $request->input('name');
        $grade = $request->input('grade');
        $section = $request->input('section');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $present = $request->input('status');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
        $setOfNames = explode(' ', $sanitizedName);

        if ($startTime) {
            $startTime = Carbon::createFromFormat('H:i', $startTime)->format('H:i'); 
        }
    
        if ($endTime) {
            $endTime = Carbon::createFromFormat('H:i', $endTime)->format('H:i'); 
        }

        $classAttendances = Auth::user()->attendanceStudents()
            ->when($setOfNames, function($q, $setOfNames){
                foreach ($setOfNames as $name) {
                    $name = trim($name);
                    $q->where('name', 'LIKE', "%{$name}%");
                }
            })
            ->when($grade, function($q, $grade){
                return $q->where('grade', $grade);
            })
            ->when($section, function($q, $section) {
                return $q->where('section', $section);
            });
        if ($startDate && $endDate) {
            $classAttendances->where('date', '>=', $startDate)
                             ->where('date', '<=', $endDate);
        } elseif ($startDate) {
            $classAttendances->where('date', '>=', $startDate);
        } elseif ($endDate) {
            $classAttendances->where('date', '<=', $endDate);
        }
        if (isset($present)) {
            $classAttendances->where('present', $present);
        }
    
        if ($startTime && $endTime) {
            $classAttendances->where('time', '>=', $startTime)
                             ->where('time', '<=', $endTime);
        } elseif ($startTime) {
            $classAttendances->where('time', '>=', $startTime);
        } elseif ($endTime) {
            $classAttendances->where('time', '<=', $endTime);
        }
        $classAttendances = $classAttendances->paginate(30)
        ->appends($request->all());

        dd($classAttendances->toArray());

        return view('teachers.class_attendance', compact('classAttendances'));
    }

    public function updateClassAttendance(Request $request, $id){


        Auth::user()->attendanceStudents()
        ->where('attendance_student_teacher.id', $id)
        ->update([
            'present' => $request->present
        ]);

        return back()->with('success', 'Student updated successfully');
    }
}
