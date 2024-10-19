<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Cache;
use Illuminate\Console\Command;
use Session;

class StoreMorningAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:store-morning-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'store morning attendance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $students = Cache::get('students');

        if($students){
            foreach($students as $key => $student){
                Attendance::create([
                    'student_id' => $key,
                    'check_in_time' => $student['check_in_time'],
                    'date' => $student['date'],
                    'status_morning' => ($student['status']) ? 'present' : 'absent',
                ]);
            }
            Cache::forget('students');
        }
        else{
            $this->info('No students found in cache to process for morning.');
        }

        

    }
}
