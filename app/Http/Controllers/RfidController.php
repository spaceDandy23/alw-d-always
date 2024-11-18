<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSectionTeacher;
use App\Models\RfidLog;
use App\Models\SchoolYear;

use App\Models\Section;
use App\Models\Student;
use App\Models\Tag;

use Auth;
use Illuminate\Http\Request;
use Session;




class RfidController extends Controller
{
    public function index(){


        $rfidLogs = RfidLog::latest('date')
        ->whereHas('student', function($q){

            if(Auth::user()->isAdmin()){
                return $q->where('students.school_year_id', SchoolYear::where('is_active', true)->first()->id ?? '');
    
            }
            return $q->where('students.school_year_id', SchoolYear::latest()->first()->id ?? '');
    
            
    
        })
        ->paginate(30);
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



        $rfidTag = $request->rfid_tag;


            

        $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
        $setOfNames = explode(' ', $sanitizedName);


        $rfidLogs = RfidLog::
        when($setOfNames, function($q, $setOfNames) {
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
        })
        ->when($rfidTag, function ($q, $rfidTag){
            return $q->whereHas('tag', function($q) use($rfidTag){
                $q->where('rfid_tag', $rfidTag);
            });
        });

        
        if(Auth::user()->isAdmin()){
            $rfidLogs->whereHas('student', function($q){
                $q->where('school_year_id', SchoolYear::where('is_active', true)->first()->id ?? '');

            });

        }
        elseif(Auth::user()->isTeacher()){
            $rfidLogs->whereHas('student', function($q){
                $q->where('school_year_id', SchoolYear::latest()->first()->id ?? '');

            });


        }


        $rfidLogs = $rfidLogs
                    ->paginate(30)
                    ->appends($request->all());


        return view('rfid.rfid_logs', compact('rfidLogs'));


    }

    public function verify(Request $request){

        if($request->isMethod('post')){


            if(Auth::user()->isTeacher()){
                return $this->verifyFromTeacher($request);
            }

            $currentHour = now()->format('H');
            // if ($currentHour <= 17 && $currentHour > 6) {
                if(true){
                $activeSchoolYear = SchoolYear::latest()->first();
                
                
                $tag = Tag::where('rfid_tag', $request->rfid_tag)->first();
                if(!$tag){
                    return response()->json([
                        'success' => false,
                        'message' => 'RFID tag is not registered',
                    ]);
                }
                $studentTag = Student::where('tag_id', $tag->id)
                ->where('school_year_id', $activeSchoolYear->id)
                ->with('section')
                ->first();

                if(!$studentTag){
                    return response()->json([
                        'success' => false,
                        'message' => 'Old Student RFID tag',
                    ]);
                }
                $todayDate = now()->format('Y-m-d');
              

                $student = RfidLog::where('student_id', $studentTag->id)
                ->where('date', $todayDate)
                ->latest()
                ->first();



                if ($student) {
                    if (!$student->check_out) {
                        $student->update(['check_out' => now()->format('H:i:s')]);
                        // $output = $this->message($studentTag->student->id, 'student left');
                    } else {
                        RfidLog::create([
                            'student_id' => $studentTag->id,
                            'check_in' => now()->format('H:i:s'),
                            'date' => $todayDate,
                            'tag_id' => $studentTag->tag->id
                        ]);
                        // $output = $this->message($studentTag->student->id, 'student went in');
                    }
                } else {
                    RfidLog::create([
                        'student_id' => $studentTag->id,
                        'check_in' => now()->format('H:i:s'),
                        'date' => $todayDate,
                        'tag_id' => $studentTag->tag->id
                    ]);
                    
                    // $output = $this->message($studentTag->student->id, 'student went in');
                }



                if($currentHour < 12){

                    Attendance::updateOrCreate(
                    ['student_id' => $studentTag->id,
                                'date'=> now()->format('Y-m-d')],
                        ['status_morning' => 'present']


                    );
                }
                
                if($currentHour >= 12){

                    Attendance::updateOrCreate(
                    ['student_id' => $studentTag->id,
                                'date'=> now()->format('Y-m-d')],
                        ['status_lunch' => 'present']

                    );
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Success',
                    'student' => $studentTag,

                ]);
            }
            // return response()->json([
            //     'success' => false,
            //     'message'=> 'di pwede',

            // ]);
            
        }

        if($request->section){
            $sectionId = Section::where('grade',$request->section[0])
            ->where('section', $request->section[2])
            ->first()
            ->id;
            
            Session::put('section_id', $sectionId);
            return view('rfid.rfid_scan', compact('sectionId'));

        }

        return view('rfid.rfid_scan');

    }
    public function message($studentID, $message){

        $outputArr = [];
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



            array_push($outputArr, $guardian);
        }
        return $outputArr;
    }

    public function verifyFromTeacher($request){


        $tag = Tag::where('rfid_tag', $request->rfid_tag)->first();
        if(!$tag){
            return response()->json([
                'success' => false,
                'message' => 'tag not registered'
            ]);
        }



        $student = Student::where('section_id', Session::get('section_id'))
        ->where('tag_id', $tag->id)
        ->with('section')
        ->first();

        if(!$student){
            return response()->json([
                'success' => false,
                'message' => 'Student not in this class'
            ]);
        }

        $notEnrolled = Auth::user()
        ->students()
        ->where('student_id', $student->id)
        ->where('enrolled', false)
        ->first();

        if($notEnrolled){
            return response()->json([
                'success' => false,
                'message' => 'Student not enrolled in this class'
            ]);

        }

        $dateNow = now()->format('Y-m-d');

        $isExist = AttendanceSectionTeacher::
        where('date', $dateNow)
        ->where('student_id', $student->id)
        ->first();


        if(!$isExist){
            AttendanceSectionTeacher::create([
                'teacher_id' => Auth::id(),
                'student_id' => $student->id,
                'section_id' => $student->section->id,
                'present' => true,
                'date' => $dateNow,
                'time' => now()->format('H:i:s')
            ]);
        }

        else{
            $isExist->update(['present' => true]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Success',
            'student' => $student,
            'from_teacher' => true,

        ]);

    }
}
