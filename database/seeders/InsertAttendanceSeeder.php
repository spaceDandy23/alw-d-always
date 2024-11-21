<?php

namespace Database\Seeders;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsertAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $twoDaysAgo = Carbon::now()->subDays(2)->toDateString();
        $yesterday = Carbon::now()->subDay()->toDateString();
        $studentIds = DB::table('students')->pluck('id'); 

        foreach ($studentIds as $studentId) {
            DB::table('attendances')->insert([
                [
                    'student_id' => $studentId,
                    'date' => $twoDaysAgo,
                    'status_morning' => 'absent',
                    'status_lunch' => 'absent',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'student_id' => $studentId,
                    'date' => $yesterday,
                    'status_morning' => 'present',
                    'status_lunch' => 'absent',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }


        $this->command->info("Absent attendance records have been inserted for the previous two days.");
    }
}
