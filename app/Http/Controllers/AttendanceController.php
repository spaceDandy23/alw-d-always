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

        $startMonth = intval($request->input('start_month'));
        $endMonth = intval($request->input('end_month'));
        $startDay = intval($request->input('start_day'));
        $endDay = intval($request->input('end_day'));

        if($startMonth > $endMonth){
            return redirect()->back()->with('error', 'End month should be after the start month');
        }

        $startMonthDay = sprintf('%02d-%02d', $startMonth, $startDay);
        $endMonthDay = sprintf('%02d-%02d', $endMonth, $endDay);

        $grade = $request->input('grade');
        $section = $request->input('section');
        $schoolYearId = $request->input('school_year');

        
        $attendanceRecords = Attendance::join('students', 'attendances.student_id', '=', 'students.id')
        ->whereRaw("DATE_FORMAT(date, '%m-%d') BETWEEN '{$startMonthDay}' AND '{$endMonthDay}'")
        ->when($grade, function($q, $grade){
            return  $q->where('students.grade', $grade);
        })
        ->when($section, function($q, $section){
            return  $q->where('students.section', $section);
        })
        ->when($schoolYearId, function ($q, $schoolYearId){
            return $q->where('students.school_year_id', $schoolYearId);
        })
        ->orderBy('students.name','asc')
        ->orderBy('attendances.date','asc')
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

        
       $startDateEndDate = "{$startMonthDay} - {$endMonthDay}";
       $schoolYears = SchoolYear::all();

        return view('reports.reports', compact('attendanceRecords','studentsTotalAbsents','startDateEndDate','schoolYears'))->with('success', 'Filtered Successfully');
        
    }

}
