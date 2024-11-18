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


        $attendances = Attendance::
        when($fromExcuse, function($q) {
            $q->where(function ($query) {
                $query->whereIn('status_morning', ['excused', 'absent'])
                      ->orWhereIn('status_lunch', ['excused', 'absent']);
            });
        })
        ->when($setOfNames, function($q, $setOfNames){
            foreach ($setOfNames as $name) {
                $name = trim($name);
                $q->whereHas('student', function ($query) use ($name) {
                    $query->where('name', 'LIKE', "%{$name}%");
                });
            }

        })
        ->when($section, function($q, $section) {
            return $q->whereHas('student', function ($query) use ($section) {
                $query->whereHas('section', function($query) use ($section){
                    $query->where('section', $section);
                });
            });
        })
        ->when($grade, function($q, $grade) {
            return $q->whereHas('student', function ($query) use ($grade) {
                $query->whereHas('section', function($query) use ($grade){
                    $query->where('grade', $grade);
                });
            });
        })
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->whereBetween('date', [$startDate, $endDate]);
        });





        if(Auth::user()->isAdmin()){
            $attendances->whereHas('student', function($q){
                return $q->where('school_year_id', SchoolYear::where('is_active', true)->first()->id ?? '');
            });
    
        }

        elseif(Auth::user()->isTeacher()){
            $attendances->whereHas('student', function($q){
                return $q->where('school_year_id', SchoolYear::latest()->first()->id ?? '');
            });

        }

        
        if(!$fromExcuse){
        $attendances->join('students', 'attendances.student_id', '=', 'students.id')
        ->selectRaw('
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
            ->with('student')
            ->paginate(30)
            ->appends($request->all());
            return view('attendances.cancel_excuse_students', compact('attendances'));

        }
        $fatherlessChild = $this->getStudentsAttendance($name, $grade, $section,$startDate, $endDate);
        $getOverallAttendance = $fatherlessChild->get();
        $attendances = $fatherlessChild->paginate(30)->appends($request->all());
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


    public function attendances(Request $request){

        $attendances = Attendance::where(function ($query) {
            $query->whereIn('status_morning', ['excused', 'absent'])
                  ->orWhereIn('status_lunch', ['excused', 'absent']);
        })
        ->whereHas('student', function ($q) {
            $activeSchoolYearId = SchoolYear::where('is_active', true)->value('id');
            $q->where('students.school_year_id', $activeSchoolYearId);
        })
        ->limit(30)
        ->get();
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



        if(!$request->cancel_lunch){
            $records->update(['status_morning' => 'present']);
        }
        elseif($request->cancel_lunch && !$request->cancel_morning){
            $records->where('status_morning', 'present')->update(['status_lunch' => 'present']);
            $records->each(function($record) {
                $record->student->rfidLogs()->update(['check_out' => now()->format('12:00:00')]);
            });
        }
        else{
            $records->update(['status_morning' => 'present',
                                            'status_lunch' => 'present']);
        }

        return back()->with('success', 'Class cancelled successfully');


    }

}
