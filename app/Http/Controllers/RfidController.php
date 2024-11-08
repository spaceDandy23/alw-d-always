<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RfidLog;
use App\Models\SchoolYear;

use App\Models\Student;
use App\Models\Tag;

use Illuminate\Http\Request;
use App\Models\Notification;




class RfidController extends Controller
{
    public function index(){
        $rfidLogs = RfidLog::whereHas('student', function($q){
            return $q->where('students.school_year_id', SchoolYear::where('is_active', true)->first()->id);

        })
        ->paginate(20);
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
        ->where('students.school_year_id', SchoolYear::where('is_active', true)->first()->id)
        ->paginate(20)
        ->appends($request->all());

    
        return view('rfid.rfid_logs', compact('rfidLogs'));


    }

    public function verify(Request $request){

        if($request->isMethod('post')){
            $currentHour = now()->format('H');
            if ( true) {
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


                $student = RfidLog::where('student_id', $studentTag->student->id)
                ->where('date', $todayDate)
                ->latest()
                ->first();
                if ($student) {
                    if (!$student->check_out) {
                        $student->update(['check_out' => now()->format('H:i:s')]);
                        $this->message($studentTag->student->id, 'student left');
                    } else {
                        RfidLog::create([
                            'student_id' => $studentTag->student->id,
                            'check_in' => now()->format('H:i:s'),
                            'date' => $todayDate,
                        ]);
                        $this->message($studentTag->student->id, 'student went in');
                    }
                } else {
                    RfidLog::create([
                        'student_id' => $studentTag->student->id,
                        'check_in' => now()->format('H:i:s'),
                        'date' => $todayDate,
                    ]);
                    $this->message($studentTag->student->id, 'student went in');
                }


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
    public function message($studentID, $message){


        $student = Student::find($studentID);

        foreach($student->guardians as $guardian){
            $ch = curl_init();
            $parameters = array(
                'apikey' => env('SEMAPHORE_API_KEY'), 
                'number' => $guardian->contact_info,
                'message' => $message,
                'sendername' => 'Alwad'
            );

            curl_setopt( $ch, CURLOPT_URL,'https://api.semaphore.co/api/v4/messages' );
            curl_setopt( $ch, CURLOPT_POST, 1 );


            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $parameters ) );

            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            $output = curl_exec( $ch );
            curl_close ($ch);

            Notification::create([
                'guardian_id' => $guardian->id, 
                'student_id' => $student->id, 
                'message' => $message]);
        }

    }
}
