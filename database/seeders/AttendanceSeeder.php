<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\RfidLog;
use App\Models\Student;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $students = Student::all();
        $startDate = Carbon::createFromDate(2024, 8, 1);
        $endDate = Carbon::createFromDate(2024, 10, 31);
        $dates = $startDate->daysUntil($endDate);

        foreach ($dates as $date) {
            foreach ($students as $student) {
                $rfidLogs = RfidLog::where('student_id', $student->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->get();

                $statusMorning = 'absent';
                $statusLunch = 'absent';
                foreach ($rfidLogs as $log) {
                    if (Carbon::parse($log->check_in_time)->format('H') < 12) {
                        $statusMorning = 'present';
                    } else {
                        $statusLunch = 'present';
                    }
                }

                Attendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'status_morning' => $statusMorning,
                        'status_lunch' => $statusLunch,
                    ]
                );
            }
        }
    }
}
