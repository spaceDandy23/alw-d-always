<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;


class NotificationController extends Controller
{
    //

    public function index(){

        $notifications = Notification::paginate(20);
        return view('notifications.notifications_list', compact('notifications'));
    }
    public function messageParent(Request $request, Student $student){

        $request->validate([
            'message' => 'required|min:1|max:160|regex:/^[a-zA-Z0-9\s.,?!]+$/'
        ]);

        if($student->guardian){

            return back()->with('error', 'Student doesnt have an associated guardian');
        }


        $ch = curl_init();
        $parameters = array(
            'apikey' => env('SEMAPHORE_API_KEY'), 
            'number' => $student->guardian->contact_info,
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
            'guardian_id' => $student->guardian->id, 
            'student_id' => $student->id, 
            'message' => $request->message]);
        dd($output);

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
            return $q->where('notifications.created_at', '>=', $startDate)
                      ->where('notifications.created_at', '<=', $endDate);
        })
        ->paginate(20)
        ->appends($request->all());





        return view('notifications.notifications_list', compact('notifications'));
    }
}
