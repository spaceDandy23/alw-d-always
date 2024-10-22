<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::paginate(20);
        return view('attendances.attendances_list', compact('attendances'));
        
    }
    public function search(Request $request){
        $request->validate([        
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);



        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $name = $request->input('name');
        $grade = $request->input('grade');
        $section = $request->input('section');


        $attendances = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->whereBetween('date', [$startDate, $endDate]);
        })
    
        ->when($grade, function($q, $grade){
            return  $q->where('students.grade', $grade);
        })
        ->when($name, function($q, $name){
            return  $q->where('students.name','LIKE', "%{$name}%");
        })
        ->when($section, function($q, $section){
            return  $q->where('students.section', $section);
        })
        ->orderBy('students.name','asc')
        ->orderBy('attendances.date','asc')
        ->paginate(5)
        ->appends($request->all());

        return view('attendances.attendances_list', compact('attendances'));




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
    public function reports(){
        $schoolYears = SchoolYear::all();
        return view('reports.reports', compact('schoolYears'));
        
    }
    public function filterAttendance(Request $request)
    {
        
        $request->validate([        
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);



        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $name = $request->input('name');
        $grade = $request->input('grade');
        $section = $request->input('section');


        
        $attendanceRecords = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->whereBetween('date', [$startDate, $endDate]);
        })
        ->when($grade, function($q, $grade){
            return  $q->where('students.grade', $grade);
        })
        ->when($section, function($q, $section){
            return  $q->where('students.section', $section);
        })
        ->when($name, function($q, $name){
            return  $q->where('students.name', 'LIKE', "%{$name}%");
        })
        ->orderBy('students.name','asc')
        ->orderByRaw("DATE_FORMAT(date, '%m-%d') ASC")
        ->get()
        ->map(function ($attendance){
            $attendance->date = Carbon::parse($attendance->date)->format('m/d');
            $totalAbsences = 0;
        

            if ($attendance->status_morning == 'absent') {
                $totalAbsences += 0.5; 
            }
        
            if ($attendance->status_lunch == 'absent') {
                $totalAbsences += 0.5; 
            }
        
            $attendance->total_absences = $totalAbsences;
            return $attendance;
        });

        $studentsTotalAbsents = $attendanceRecords->groupBy('student_id')->map(function ($records, $studentId) {
            return [
                'student' => Student::where('id', $studentId)->first(),
                'total_absences' => $records->sum('total_absences'),
            ];
        });

        
       $startDateEndDate = "{$startDate} - {$endDate}";
       $schoolYears = SchoolYear::all();

        return view('reports.reports', compact('attendanceRecords','studentsTotalAbsents','startDateEndDate','schoolYears'))->with('success', 'Filtered Successfully');
        
    }

}
