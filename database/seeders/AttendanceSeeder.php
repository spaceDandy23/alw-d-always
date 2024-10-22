<?php

namespace Database\Seeders;

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
        $students = [33, 34, 35]; // Array of student IDs
        $startDate = Carbon::createFromDate(2024, 10, 1);
        $endDate = Carbon::createFromDate(2024, 10, 31);

        // Loop through each day in October
        foreach ($startDate->daysUntil($endDate) as $date) {
            foreach ($students as $studentId) {
                // Example: Mark every student present in the morning and absent at lunch
                DB::table('attendances')->insert([
                    'student_id' => $studentId,
                    'check_in_time' => '08:00:00', // Example check-in time
                    'date' => $date->toDateString(),
                    'status_morning' => 'present', // Example status
                    'status_lunch' => 'absent', // Example status
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
