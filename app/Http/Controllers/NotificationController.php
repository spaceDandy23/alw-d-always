<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Notification;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;


class NotificationController extends Controller
{
    //

    public function index(){

        $notifications = Notification::whereHas('guardian', function($q){
            return $q->whereHas('students', function($q){
                return $q->where('school_year_id', SchoolYear::where('is_active', true)->first()->id ?? '');
            });

        })
        ->paginate(20);
        return view('notifications.notifications_list', compact('notifications'));
    }
    public function messageParent(Student $student, Request $request){

        $request->validate([
            'message' => 'required|min:1|max:160|regex:/^[a-zA-Z0-9\s.,?!]+$/'
        ]);
        $guardianIds = $request->input('guardian_ids', []);

        if(!$guardianIds){
            return back()->with('error', 'Please fill out at least one checkbox');
        }


        $guardians = Guardian::whereIn('id', $guardianIds)->get();

        foreach($guardians as $guardian){


            $ch = curl_init();
            $parameters = array(
                'apikey' => env('SEMAPHORE_API_KEY'), 
                'number' => $guardian->contact_info,
                'message' => $request->message,
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
                'message' => $request->message,
                'date' => today()]);
        }


        return back()->with('success', 'Message sent sucessfully');

    }
    public function search(Request $request){


        
        $request->validate([        
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
        $name = $request->input('name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // dd($name, $grade, $section, $startDate, $endDate);
        $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
        $setOfNames = explode(' ', $sanitizedName);

        $notifications = Notification::join('guardians', 'notifications.guardian_id', '=', 'guardians.id') 
        ->when($name, function($q) use($setOfNames) {
            return $q->where(function($query) use ($setOfNames) { 
                foreach($setOfNames as $name) {
                    $name = trim($name);
                    $query->orWhere('guardians.name', 'LIKE', "%{$name}%");
                }
            });
        })
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->where('notifications.date', '>=', $startDate)
                      ->where('notifications.date', '<=', $endDate);
        })
        ->paginate(20)
        ->appends($request->all());





        return view('notifications.notifications_list', compact('notifications'));
    }
    public function massMessage(Request $request){

        $students = Student::whereIn('id', $request->absents)->get();
        foreach($students as $student){
            foreach($student->guardians as $guardian){
                $ch = curl_init();
                $parameters = array(
                    'apikey' => env('SEMAPHORE_API_KEY'), 
                    'number' => $guardian->contact_info,
                    'message' => $request->message,
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
                    'message' => $request->message]);

            }
        }

        return back()->with('success', 'Parents messaged successfully');
    }
}
