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
                    'check_in_time' => now()->format('H:i:s'),
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
