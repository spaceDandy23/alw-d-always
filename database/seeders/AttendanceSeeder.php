<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $students = DB::table('students')->pluck('id');
        $startDate = Carbon::create(2024, 9, 1);
        $endDate = Carbon::create(2024, 10, 31);

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            foreach ($students as $studentId) {
                DB::table('attendances')->insert([
                    'student_id' => $studentId,
                    'date' => $date->format('Y-m-d'),
                    'status_morning' => 'present',
                    'status_lunch' => 'present',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('rfid_logs')->insert([
                    'student_id' => $studentId,
                    'tag_id' => DB::table('students')->where('id', $studentId)->value('tag_id'),
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $date->copy()->setTime(7, 30)->format('H:i:s'), 
                    'check_out' => $date->copy()->setTime(17, 0)->format('H:i:s'), 
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $students = Student::all()->pluck('id')->toArray(); 
        if (count($students) >= 2) { 
            $randomStudents = array_rand($students, 2); 
        
            Attendance::whereIn('student_id', [$students[$randomStudents[0]], $students[$randomStudents[1]]])
                ->whereBetween('date', ['2024-09-01', '2024-09-06'])
                ->update(['status_morning' => 'absent', 'status_lunch' => 'absent']);
        }
        
    }
}
