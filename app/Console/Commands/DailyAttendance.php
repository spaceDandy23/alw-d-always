<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\SchoolYear;
use App\Models\Student;
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
        foreach($holidays as $holiday){

            $startDate = now()->setMonth($holiday->month)->setDay($holiday->day)->format('Y-m-d');

            $endDate = !$holiday->end_month && !$holiday->end_day 
            ? $startDate 
            : now()->setMonth($holiday->end_month)->setDay($holiday->end_day)->format('Y-m-d');

            if ($todayDate >= $startDate && ($endDate === $startDate || $todayDate <= $endDate)) {
                $this->info('no class');
                return;
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
