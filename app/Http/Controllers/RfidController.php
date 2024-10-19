<?php

namespace App\Http\Controllers;

use App\Models\RfidLog;
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

    public function verify(Request $request){

        if($request->isMethod('post')){
            $currentHour = now()->format('H');
            if (($currentHour >= 6 && $currentHour <= 8) || ($currentHour >=12 && $currentHour <= 17)) {

                $studentTag = Tag::where('rfid_tag', $request->input('rfid_tag'))->first();


                if(!$studentTag){
                    return response()->json([
                        'success' => false,
                        'message' => 'RFID tag is not registered',
                    ]);
                }


                $students = Cache::get('students');
                if($students){
                    $students[$studentTag->student->id]['status'] = true;
                    Cache::put('students', $students);
                }
                else{
                    foreach(Student::all() as $student){
                        $studentsSession[$student->id] = [
                            'check_in_time' => now()->format('H:i:s'),
                            'date' => now()->format('Y-m-d'),
                            'status' => $student->id === $studentTag->student->id ? true : false,
                        ];
                    }
                    Cache::put('students', $studentsSession);
                }


                RfidLog::create([

                    'student_id' => $studentTag->student->id,
                    'check_in_time' => now()->format('H:i:s'),
                    'date' => now()->format('Y-m-d')

                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Success',
                    'student' => $studentTag->student,
                    'students' => Cache::get('students'),

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
