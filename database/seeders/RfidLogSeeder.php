<?php

namespace Database\Seeders;

use App\Models\RfidLog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RfidLogSeeder extends Seeder
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
                // Generate random check-in time between 7:00 AM and 6:00 PM
                $checkInTime = Carbon::createFromFormat('H', rand(7, 17))
                    ->setMinute(rand(0, 59))
                    ->format('H:i:s');

                RfidLog::create([
                    'student_id' => $student->id,
                    'time' => $checkInTime,
                    'date' => $date->format('Y-m-d'),
                ]);
            }
        }
    }
}
