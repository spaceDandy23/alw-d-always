<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
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

        return view('reports.reports');


        
    }
    public function filterAttendance(Request $request)
    {
        $request->validate([
            'start_day' => 'required',
            'start_month' => 'required',
            'end_day' => 'required',
            'end_month' => 'required',
            'grade' => 'nullable',
            'section' => 'nullable',
        ]);
        $startDate = sprintf('%04d-%02d-%02d', date('Y'), $request->start_month, $request->start_day);
        $endDate = sprintf('%04d-%02d-%02d', date('Y'), $request->end_month, $request->end_day);
        $attendanceRecords = Attendance::query()
        ->when($request->input('grade'), function ($query) use ($request) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade', $request->grade);
            });
        })
        ->when($request->input('section'), function ($query) use ($request) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('section', $request->section);
            });
        })
        ->orderBy(Student::select('grade')->whereColumn('students.id', 'attendances.student_id'), 'ASC')
        ->get();
    
        return view('reports.reports', compact('attendanceRecords'));
    }
}
