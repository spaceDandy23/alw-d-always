<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\SchoolYear;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-attendance';

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
        $activeSchoolYear = SchoolYear::where('is_active', true)->first();
        $todayDate = now()->format('Y-m-d');

        $today = now();
        if ($today->isWeekend()) {
            return;
        }

        $holidays = Holiday::all();
        foreach ($holidays as $holiday) {

            $startDate = Carbon::now()->setMonth($holiday->month)->setDay($holiday->day)->startOfDay();
    

            $endDate = (!$holiday->end_month && !$holiday->end_day)
                ? $startDate
                : Carbon::now()->setMonth($holiday->end_month)->setDay($holiday->end_day)->endOfDay();

            if (today()->between($startDate, $endDate)) {
                
                $this->info('No class');
                return true; 
            }
        }
    



        foreach (Student::where('school_year_id', $activeSchoolYear->id)->get() as $student) {
            Attendance::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => $todayDate,
                ],
                [
                    'status_morning' => 'absent',
                    'status_lunch' => 'absent',
                ]
            );
        }

        $this->info('Attendance records initialized for all students.');
    }
}
