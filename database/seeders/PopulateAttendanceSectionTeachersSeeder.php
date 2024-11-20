<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PopulateAttendanceSectionTeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $students = DB::table('student_teacher')
            ->where('teacher_id', 5)
            ->where('enrolled', 1) 
            ->get();

        $startDate = Carbon::create(2024, 9, 1);
        $endDate = Carbon::create(2024, 10, 31);

        foreach ($students as $student) {
            $date = $startDate->copy();
            while ($date <= $endDate) {
                DB::table('attendance_section_teachers')->insert([
                    'student_id' => $student->student_id,
                    'teacher_id' => 5,
                    'section_id' => 79, 
                    'date' => $date->toDateString(),
                    'present' => 1,  
                    'time' => $date->toTimeString(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $date->addDay();  
            }
        }

        $this->command->info("Attendance records have been populated for students from September 1 to October 31.");
    }
}