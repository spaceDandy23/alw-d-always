<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {

        return view('attendances.attendances_list');
        
    }

    public function getStudentsAttendance($name = '', $grade = '', $section = '', $startDate = '', $endDate = '', $fromExcuse = '')
    {
        $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
        $setOfNames = explode(' ', $sanitizedName);


        $attendances = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->when($setOfNames, function($q, $setOfNames){
            foreach($setOfNames as $name){
                $name = trim($name);
                $q->orWhere('students.name', 'LIKE', "%{$name}%");
            }

        })
        ->when($grade, function($q, $grade){
            return $q->where('students.grade', $grade);
        })
        ->when($section, function($q, $section){
            return $q->where('students.section', $section);
        })
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->whereBetween('date', [$startDate, $endDate]);
        })
        ->when($fromExcuse,function($q) {
            $q->whereIn('attendances.status_morning', ['excused', 'absent'])
              ->orWhereIn('attendances.status_lunch', ['excused', 'absent']);
        });




        if(Auth::user()->isAdmin()){
            $attendances->where('students.school_year_id', SchoolYear::where('is_active', true)->first()->id);

        }
        elseif(Auth::user()->isTeacher()){
            $attendances->where('students.school_year_id', SchoolYear::latest()->first()->id);

        }

        
        if(!$fromExcuse){
        $attendances->selectRaw('
        students.id as student_id,
        students.name as student_name,
        SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) +
        SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END) as total_present,
        SUM(CASE WHEN status_morning = "absent" THEN 0.5 ELSE 0 END) +
        SUM(CASE WHEN status_lunch = "absent" THEN 0.5 ELSE 0 END) as total_absent,
        COUNT(attendances.id) as total_attendance_records,
        (SUM(CASE WHEN status_morning = "present" THEN 0.5 ELSE 0 END) + 
         SUM(CASE WHEN status_lunch = "present" THEN 0.5 ELSE 0 END)) / 
         COUNT(attendances.id) as average_days_present
        ')
        ->groupBy('students.id', 'students.name');

        }
    
        return $attendances; 
    }
    public function search(Request $request){
        $request->validate([        
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);



        $name = $request->input('name');
        $grade = $request->input('grade');
        $section = $request->input('section');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $fromExcuse = $request->input('from_cancel_excuse');
        if($fromExcuse){

            $attendances = $this->getStudentsAttendance($name, $grade, $section, $startDate, $endDate, $fromExcuse)
            ->select(
    'attendances.id as id',
             'students.id as student_id', 
             'students.name as name',
             'students.grade as grade',
             'attendances.status_morning as status_morning',
             'attendances.status_lunch as status_lunch',
             'attendances.date as date')
            ->paginate(20)
            ->appends($request->all());
            return view('attendances.cancel_excuse_students', compact('attendances'));


        }
        $fatherlessChild = $this->getStudentsAttendance($name, $grade, $section,$startDate, $endDate);
        $getOverallAttendance = $fatherlessChild->get();
        $attendances = $fatherlessChild->paginate(10)->appends($request->all());
        $totalStudents = $attendances->total();



        $totalAbsent = 0;
        $totalPresent = 0;
        $getOverallAttendance->each(function ($attendance) use (&$totalAbsent, &$totalPresent){
            $totalAbsent += $attendance->total_absent;
            $totalPresent += $attendance->total_present;
        });

        $totalNumbers =['overallAbsent' => $totalAbsent, 
                        'overallPresent' => $totalPresent,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'totalStudents' => $totalStudents ];


        return view('attendances.attendances_list', compact('attendances', 'totalNumbers'));


    }
    public function update(Request $request, Attendance $student){

        $student->update([
            'status_morning' => $request->input('status_morning'),
            'status_lunch' => $request->input('status_lunch'),

        ]);
        if($request->input('from_profile')){
            return redirect()->back()->with('success', 'Student Updated successfully');
        }

        return redirect()->route('attendances.index')->with('success', 'Student Updated successfully');

    }


    public function attendances(){

        $attendances = Attendance::latest()
        ->whereIn('status_morning', ['excused', 'absent'])
        ->orWhereIn('status_lunch', ['excused', 'absent'])
        ->whereHas('student', function($q){
            return $q->where('students.school_year_id', SchoolYear::where('is_active', true)->first()->id);

        })
        ->paginate(40);

        return view('attendances.cancel_excuse_students', compact('attendances'));

    }

    public function excuseCancel(Request $request){

        if(!$request->attendance){
            return back()->with('error', 'No checkbox selected');
        }
        foreach($request->attendance as $id => $value){

            $record = Attendance::find($id);

            if (isset($value['status_morning'])) {
                $record->status_morning = $value['status_morning'];
            }
            else{

                $record->status_morning = 'absent';

            }
            
            if (isset($value['status_lunch'])) {
                $record->status_lunch = $value['status_lunch'];
            }
            else{
                $record->status_lunch = 'absent';
            }
            $record->save();
        }


        return redirect()->back()->with('success', 'Students excused successfully');
    }
    public function cancelClassSession(Request $request){


        $records = Attendance::where('date', now()->format('Y-m-d'));



        if($request->cancel_morning){
            $records->update(['status_morning' => 'present']);
        }
        elseif($request->cancel_lunch){
            $records->update(['status_lunch' => 'present']);
        }
        else{
            $records->update(['status_morning' => 'present',
                                            'status_lunch' => 'present']);
        }

        return back();


    }

}
