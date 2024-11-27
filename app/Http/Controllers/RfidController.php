<?php

namespace App\Http\Controllers;

use App\Jobs\CheckOccasionJob;
use App\Jobs\SendMessageJob;
use App\Models\Attendance;
use App\Models\AttendanceSectionTeacher;
use App\Models\Holiday;
use App\Models\RfidLog;
use App\Models\SchoolYear;

use App\Models\Section;
use App\Models\Student;
use App\Models\Tag;

use Auth;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;




class RfidController extends Controller
{
    public function index(){

        $activeSchoolYear = Session::get(Auth::id()) ?? SchoolYear::where('is_active', true)->first();


        $rfidLogs = RfidLog::latest('date')
        ->whereHas('student', function($q) use($activeSchoolYear){

            return $q->when($activeSchoolYear, function($q, $activeSchoolYear){
                return $q->where('school_year_id', $activeSchoolYear->id);

            });
    
            
    
        })
        ->paginate(30);
        return view('rfid.rfid_logs', compact('rfidLogs'));
    }
    public function search(Request $request){
        
        $request->validate([        
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

        $activeSchoolYear = Session::get(Auth::id()) ?? SchoolYear::where('is_active', true)->first();

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
        })
        ->whereHas('student', function($q) use ($activeSchoolYear){
                $q->when($activeSchoolYear, function($q, $activeSchoolYear) {
                    return $q->where('school_year_id', $activeSchoolYear->id);

                });
            })
        ->paginate(30)
        ->appends($request->all());


        return view('rfid.rfid_logs', compact('rfidLogs'));


    }

    public function verify(Request $request){

        if($request->isMethod('post')){

            $now = now();

            if($this->checkHoliday()){

                return response()->json(['success' => false, 'message' => 'no class']);
            }

            if(Auth::user()->isTeacher()){
                return $this->verifyFromTeacher($request);
            }

            $currentHour = now()->format('H');
            // if ($currentHour <= 17 && $currentHour > 6) {
                if(true){
                $activeSchoolYear = SchoolYear::where('is_active', true)->first();
                
                
                $tag = Tag::where('rfid_tag', $request->rfid_tag)->latest()->first();
                if(!$tag){
                    return response()->json([
                        'success' => false,
                        'message' => 'RFID tag is not registered',
                    ]);
                }


                $studentTag = Student::where('tag_id', $tag->id)
                ->when($activeSchoolYear, function($q, $activeSchoolYear){
                    return $q->where('school_year_id', $activeSchoolYear->id);
                })
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
                        // $this->message($studentTag->id, Cache::get('messages')['secondMessage'] . ' ' . now());
                    } else {
                        RfidLog::create([
                            'student_id' => $studentTag->id,
                            'check_in' => now()->format('H:i:s'),
                            'date' => $todayDate,
                            'tag_id' => $studentTag->tag->id
                        ]);
                        // $this->message($studentTag->id, Cache::get('messages')['firstMessage'] . ' ' . now());
                    }
                } else {
                    RfidLog::create([
                        'student_id' => $studentTag->id,
                        'check_in' => now()->format('H:i:s'),
                        'date' => $todayDate,
                        'tag_id' => $studentTag->tag->id
                    ]);
                    
                    // $this->message($studentTag->id, Cache::get('messages')['firstMessage'] . ' ' . now());
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
            $explodedSection = explode('-', $request->section);
            $sectionId = Section::where('grade',$explodedSection[0])
            ->where('section', $explodedSection[1])
            ->first()
            ->id;
            
            Session::put('section_id', $sectionId);
            return view('rfid.rfid_scan', compact('sectionId'));

        }

        return view('rfid.rfid_scan');

    }
    public function message($studentID, $message){


        $student = Student::find($studentID);



        foreach($student->guardians as $guardian){
            SendMessageJob::dispatch($guardian->contact_info, $message);
        }

    }

    public function verifyFromTeacher($request){


        $tag = Tag::where('rfid_tag', $request->rfid_tag)->first();
        if(!$tag){
            return response()->json([
                'success' => false,
                'message' => 'tag not registered'
            ]);
        }

        $activeSchoolYear = SchoolYear::where('is_active', true)->first();


        $student = Student::where('section_id', Session::get('section_id'))
        ->when($activeSchoolYear, function($q, $activeSchoolYear){
            return $q->where('school_year_id', $activeSchoolYear->id);
        })
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

    public function checkHoliday(){

        $today = now();
        if ($today->isWeekend()) {
            return false;
        }
        
        $holidays = Holiday::all();
        foreach ($holidays as $holiday) {

            $startDate = Carbon::now()->setMonth($holiday->month)->setDay($holiday->day)->startOfDay();
    

            $endDate = (!$holiday->end_month && !$holiday->end_day)
                ? $startDate
                : Carbon::now()->setMonth($holiday->end_month)->setDay($holiday->end_day)->endOfDay();

            if (today()->between($startDate, $endDate)) {
                return true; 
            }
        }

        return false;
    
    }
}
