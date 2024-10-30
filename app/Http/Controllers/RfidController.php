<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RfidLog;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Tag;
use Cache;
use Illuminate\Http\Request;
use Session;


class RfidController extends Controller
{
    public function index(){
        $rfidLogs = RfidLog::paginate(20);
        return view('rfid.rfid_logs', compact('rfidLogs'));
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


        $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
        $setOfNames = explode(' ', $sanitizedName);

        $rfidLogs = RfidLog::join('students', 'rfid_logs.student_id', '=', 'students.id')
        ->when($setOfNames, function($q, $setOfNames){
            foreach($setOfNames as $name){
                $name = trim($name);
                $q->orWhere('students.name', 'LIKE', "%{$name}%");
            }

        })
        ->when($section, function($q, $section){
            return $q->where('students.section', '=', $section);
        })
        ->when($grade, function($q, $grade){
            return $q->where('students.grade', '=', $grade);
        })
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->whereBetween('date', [$startDate, $endDate]);
        })
        ->paginate(20)
        ->appends($request->all());

    
        return view('rfid.rfid_logs', compact('rfidLogs'));


    }

    public function verify(Request $request){

        if($request->isMethod('post')){
            $currentHour = now()->format('H');
            if ($currentHour <= 17 && $currentHour > 6 ) {
                $activeSchoolYear = SchoolYear::where('is_active', true)->first();



                $studentTag = Tag::where('rfid_tag', $request->input('rfid_tag'))
                ->whereHas('student', function ($query) use ($activeSchoolYear) {
                    $query->where('school_year_id', $activeSchoolYear->id);
                })
                ->first();

                if(!$studentTag){
                    return response()->json([
                        'success' => false,
                        'message' => 'RFID tag is not registered',
                    ]);
                }


                $todayDate = now()->format('Y-m-d');

                


                RfidLog::create([

                    'student_id' => $studentTag->student->id,
                    'time' => now()->format('H:i:s'),
                    'date' => $todayDate

                ]);



                if($currentHour < 12){

                    Attendance::updateOrCreate(
                        ['student_id' => $studentTag->student->id,
                        'date'=> now()->format('Y-m-d')],
                        ['status_morning' => 'present']

                    );
                }
                
                if($currentHour >= 12){

                    Attendance::updateOrCreate(
                        ['student_id' => $studentTag->student->id,
                        'date'=> now()->format('Y-m-d')],
                        ['status_lunch' => 'present']

                    );
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Success',
                    'student' => $studentTag->student,

                ]);
            }
            return response()->json([
                'success' => false,
                'message'=> 'di pwede',

            ]);
            
        }

        return view('rfid.rfid_scan');


    }
}
