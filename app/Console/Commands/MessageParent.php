<?php

namespace App\Console\Commands;


use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Console\Command;
use App\Models\Notification;



class MessageParent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:message-parent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $students = Attendance::where('date', now()->format('Y-m-d'))
        // ->where('status_morning', 'absent')
        // ->get();


        // if($students->isEmpty()){
        //     $this->info('no records found');
        //     return;
        // }
        // foreach($students as $student){
        //     $studentObj = Student::find($student->student_id);
        //     $ch = curl_init();
        //     $message = 'Your Son/Daughter was absent this morning';
        //     $parameters = array(
        //         'apikey' => env('SEMAPHORE_API_KEY'), 
        //         'number' => $studentObj->guardian->contact_info,
        //         'message' => $message,
        //         'sendername' => 'Alwad'
        //     );
    
        //     curl_setopt( $ch, CURLOPT_URL,'https://api.semaphore.co/api/v4/messages' );
        //     curl_setopt( $ch, CURLOPT_POST, 1 );
    
    
        //     curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $parameters ) );
    
        //     curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
        //     curl_exec( $ch );
        //     curl_close ($ch);

        //     Notification::create(['guardian_id' => $studentObj->guardian->id,
        //     'message' => $message]);

        // }


    }
}
