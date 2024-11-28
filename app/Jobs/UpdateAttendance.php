<?php

namespace App\Jobs;

use App\Models\Attendance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateAttendance implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $student;
 
    public function __construct($student)
    {
        $this->student = $student;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        
        $currentHour = now()->format('H');
        if($currentHour < 12){


            Attendance::where('student_id', $this->student->id)
            ->where('date', now()->format('Y-m-d'))
            ->update(['status_morning' => 'present']);
        }
        
        if($currentHour >= 12){

            Attendance::where('student_id', $this->student->id)
            ->where('date', now()->format('Y-m-d'))
            ->update(['status_lunch' => 'present']);
        }
    }
}
