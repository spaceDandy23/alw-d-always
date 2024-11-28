<?php

namespace App\Jobs;

use App\Models\RfidLog;
use App\Models\Student;
use Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;

class UpdateStudentAttendance implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $student;
    protected $studentLog;

    public function __construct($student, $studentLog)
    {
        $this->student = $student;
        $this->studentLog = $studentLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void 
    {
        $todayDate = today();
        if ($this->studentLog) {
            if (!$this->studentLog->check_out) {
                $this->studentLog->update(['check_out' => now()->format('H:i:s')]);
                // $this->message($this->student->id, Cache::get('messages')['secondMessage'] . ' ' . now());
            } else {
                RfidLog::create([
                    'student_id' => $this->student->id,
                    'check_in' => now()->format('H:i:s'),
                    'date' => $todayDate,
                    'tag_id' => $this->student->tag_id
                ]);
                // $this->message($this->student->id, Cache::get('messages')['firstMessage'] . ' ' . now());
            }
        } else {
            RfidLog::create([
                'student_id' => $this->student->id,
                'check_in' => now()->format('H:i:s'),
                'date' => $todayDate,
                'tag_id' => $this->student->tag_id
            ]);
            
            // $this->message($this->student->id, Cache::get('messages')['firstMessage'] . ' ' . now());
        }
    }


    public function message($studentID, $message){


        $student = Student::find($studentID);
        foreach($student->guardians as $guardian){
            SendMessageJob::dispatch($guardian->contact_info, $message)->onQueue('notification_queue');
        }

    }
}
