<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Cache;
use Illuminate\Console\Command;
use Session;

class StoreAfternoonLunch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:store-afternoon-attendance';

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
        $students = Cache::get('students');

        if($students){
            foreach($students as $key => $student){
                Attendance::updateOrCreate([
                    'student_id' => $key,
                    'date' => $student['date'],
                ],[
                    'check_in_time' => $student['check_in_time'],
                    'status_lunch' => ($student['status']) ? 'present' : 'absent'
                ] );
            }
            Cache::forget('students');
        }
        else{
            $this->info('No students found in cache to process for lunch.');
        }
    }
}
