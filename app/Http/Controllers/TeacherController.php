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
    public function index()
    {

        $activeSchoolYear = SchoolYear::latest()->first() ?? '';
    

        $activeSchoolYearId = $activeSchoolYear ? $activeSchoolYear->id : null;
    

        $attendanceTrends = AttendanceSectionTeacher::where('teacher_id', Auth::id())
            ->join('sections', 'attendance_section_teachers.section_id', '=', 'sections.id')
            ->join('students', 'attendance_section_teachers.student_id', '=', 'students.id')  
            ->where('students.school_year_id', $activeSchoolYearId)  
            ->selectRaw(
                "CONCAT('Grade ', sections.grade, ' - ', sections.section) as section_label, 
                DATE_FORMAT(date, '%Y-%m') as month, 
                SUM(present) as present, 
                COUNT(*) - SUM(present) as absent"
            )
            ->groupBy('section_label', 'month')  
            ->orderBy('month')  
            ->get()
            ->groupBy('section_label');
  

        $absentAlot = AttendanceSectionTeacher::join('students', 'attendance_section_teachers.student_id', '=', 'students.id')
            ->select('students.id', 'students.name', 'attendance_section_teachers.section_id', DB::raw('COUNT(*) as total_absent'))
            ->where('attendance_section_teachers.teacher_id', Auth::id())  
            ->where('attendance_section_teachers.present', 0)
            ->where('students.school_year_id', $activeSchoolYearId) 
            ->groupBy('students.id', 'attendance_section_teachers.section_id', 'students.name')
            ->havingRaw('COUNT(*) >= 3')
            ->get();
    

        $overallAverageAttendancePercentage = AttendanceSectionTeacher::where('teacher_id', Auth::id())
            ->join('sections', 'attendance_section_teachers.section_id', '=', 'sections.id')
            ->join('students', 'attendance_section_teachers.student_id', '=', 'students.id')  
            ->where('students.school_year_id', $activeSchoolYearId)  
            ->selectRaw(
                "CONCAT('Grade ', sections.grade, ' - ', sections.section) as section_label, 
                attendance_section_teachers.section_id as section_id, 
                (SUM(present) / COUNT(*)) * 100 as attendance_percentage"
            )
            ->groupBy('attendance_section_teachers.section_id', 'sections.grade', 'sections.section')
            ->orderBy('sections.grade')
            ->get();


        $totalStudents = Auth::user()->students()
            ->where('school_year_id', $activeSchoolYearId) 
            ->count();

        $totalDaysRecorded = $attendanceTrends->flatMap(function ($sectionData) {
            return $sectionData->pluck('date');
        })->unique()->count();
    
    

        $totalPresent = $attendanceTrends->flatMap(function ($sectionData) {
            return $sectionData->pluck('present');
        })->sum();
    
        $attendanceRate = Auth::user()->sectionAttendances()
        ->whereHas('student', function ($query) use ($activeSchoolYearId) {
            $query->where('school_year_id', $activeSchoolYearId);
        })
        ->avg('present') * 100;



        return view('teachers.teacher_dashboard', compact(
            'activeSchoolYear',
            'attendanceTrends',
            'overallAverageAttendancePercentage',
            'totalStudents',
            'totalDaysRecorded',
            'attendanceRate',
            'absentAlot'
        ));
    }
    
    
    public function classIndex(){

        $activeSchoolYearId = SchoolYear::latest()->first()->id;

        $studentsAuth = Auth::user()->students()
            ->where('school_year_id', $activeSchoolYearId) 
            ->with(['attendances' => function($query) {
                $query->where('date', today());
            }])
            ->with(['rfidLogs' => function($query) {
                $query->where('date', today());
            }])
            ->get();
        
        $groupedBySection = $studentsAuth->groupBy(function($student) {
            return "{$student->section->grade}-{$student->section->section}"; 
        });
        
        $sections = Section::all();

        return view('teachers.section_teacher_list', compact('sections', 'groupedBySection'));
    }
    
    public function storeClass(Request $request){
        $sections = $request->input('sections', []);

        $activeSchoolYear = SchoolYear::latest()->first() ?? '';

        $activeSchoolYearId = $activeSchoolYear ? $activeSchoolYear->id : null;

        if(!$sections){
            return back()->with('error', 'Please fill out at least one checkbox');
        }

        $student = Student::whereIn('section_id', $sections)->where('school_year_id',  $activeSchoolYearId)->get()->pluck('id');


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
        AttendanceSectionTeacher::where('teacher_id', Auth::id())
        ->whereIn('student_id', $studentIds)
        ->delete();

        Auth::user()->students()->detach($studentIds);

        return redirect()->route('class.index')->with('success', 'Class deleted');

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



        return response()->json([
            'success' => true
        ]);
        
    }
    public function classAttendance(){

        $attendanceSection = AttendanceSectionTeacher::where('teacher_id', Auth::id())
        ->whereHas('student', function($q){

            return $q->where('school_year_id', SchoolYear::latest()->first()->id ?? '');
        })
        ->with('section','student')
        ->paginate();
        return view('teachers.class_attendance', compact('attendanceSection'));



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
  
        $sectionIds = Section::when($grade, function($q) use ($grade) {
            $q->where('grade', $grade);
        })
        ->when($section, function($q) use ($section) {
            $q->where('section', $section);
        })
        ->pluck('id');

        $attendanceSection = AttendanceSectionTeacher::when($setOfNames, function($q) use($setOfNames){
            return $q->whereHas('student', function($q)use($setOfNames){
                    foreach ($setOfNames as $name) {
                        $name = trim($name);
                        $q->where('name', 'LIKE', "%{$name}%");
                    }
                        $q->where('school_year_id', SchoolYear::latest()->first()->id ?? '');
                        

                });
            })
            ->when($sectionIds, function($q) use($sectionIds){
                return $q->whereIn('section_id', $sectionIds);
            })
            ->where('teacher_id', Auth::id());



        if ($startDate && $endDate) {
            $attendanceSection->where('date', '>=', $startDate)
                             ->where('date', '<=', $endDate);
        } elseif ($startDate) {
            $attendanceSection->where('date', '>=', $startDate);
        } elseif ($endDate) {
            $attendanceSection->where('date', '<=', $endDate);
        }
        

        $attendanceSection->when($present, function($q, $present) {
            return $q->where('present', $present);

        });

        if ($startTime && $endTime) {
            $attendanceSection->where('time', '>=', $startTime)
                             ->where('time', '<=', $endTime);
        } elseif ($startTime) {
            $attendanceSection->where('time', '>=', $startTime);
        } elseif ($endTime) {
            $attendanceSection->where('time', '<=', $endTime);
        }
 
        $attendanceSection = $attendanceSection->paginate(30)
        ->appends($request->all());



        return view('teachers.class_attendance', compact('attendanceSection'));
    }

    public function updateClassAttendance(Request $request,  $id){

        AttendanceSectionTeacher::find($id)->update(['present' => $request->present]);
        return back()->with('success', 'Student updated successfully');
    }
}
