<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Schema;
use Storage;

class StudentController extends Controller
{

    public function register(Request $request){

        if($request->isMethod('post')){
            $request->validate([
                'rfid_tag' => 'required|numeric',  
                'first_name' => 'required|string|max:255', 
                'last_name' => 'required|string|max:255', 
                'grade' => 'required|integer|min:7|max:10',
                'section' => 'required|integer|min:1|max:3',
                
                'guardian_first_name' => 'required|string|max:255', 
                'guardian_last_name' => 'required|string|max:255', 
                'relationship' => 'required|string|max:50', 
                'phone_number' => 'nullable|string|max:15',
            ]);
            $student = Student::findOrFail($request->student_id);

            if ($student->tag) {
                return back()->with('error', 'The student already has an RFID tag assigned.');
            }
            Tag::create([
                'rfid_tag' => $request->rfid_tag,
                'student_id' => $student->id,
            ]);


            Guardian::create([
                'name' => "{$request->guardian_last_name}, {$request->guardian_first_name}",
                'student_id' => $student->id,
                'relationship_to_student' => $request->input('relationship'),
                'contact_info' => $request->input('phone_number'),
            ]);

            
            return redirect()->route('register.student.parent')->with('success', 'Student Registered');


        }
        return view('students.register_student');

    }
    public function search(Request $request){



        if($request->input('fromRegister')){
            $name = $request->input('name');
            $grade = $request->input('grade');
            $section = $request->input('section');
            $activeSchoolYear = SchoolYear::where('is_active', true)->first();
            $query = Student::query();
            $query->where('school_year_id', $activeSchoolYear->id);


            if ($name) {
                $query->where('name', 'LIKE', "%{$name}%"); 
            }
            if ($grade) {
                $query->where('grade', $grade);
            }
        
            if ($section) {
                $query->where('section', $section);
            }
        
            $studentQuery = $query->paginate(10);


            return response()->json([
                'success' => true,
                'results' => $studentQuery,
            ]);
        }

        if($request->isMethod('get')){
            $name = $request->input('name');
            $grade = $request->input('grade');
            $section = $request->input('section');
            $schoolYear = $request->input('school_year');

            $students = Student::query()
            ->when($name,function($q, $name){
                return $q->where('name', 'LIKE', "%{$name}%");
            })
            ->when($grade, function($q, $grade){
                return $q->where('grade', $grade);
            })
            ->when($section, function($q, $section){
                return $q->where('section', $section);
            })
            ->when($schoolYear, function($q, $schoolYear){
                return $q->where('school_year_id',  $schoolYear);
            })
            ->paginate(5)
            ->appends($request->all());
            $schoolYears = SchoolYear::all();
            return view('students.students_list', compact('students','schoolYears'));

        }




    }
    public function importCSV(Request $request)
    {

        $request->validate([
            'csv_file' => 'required|file',
            'school_year' => 'required'
        ]);

        SchoolYear::where('is_active', true)->update(['is_active' => false]);

        $directory = 'public/csv';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);  
        }
        $schoolYear = $request->input('school_year');

        $schoolYearRecord = SchoolYear::updateOrCreate(
            ['year' => $schoolYear],
            ['is_active' => true]
        
        );



        $file = $request->file('csv_file');
        $path = $file->storeAs('public/csv', $file->getClientOriginalName());


        $existingStudents = Student::where('school_year_id', $schoolYearRecord->id)->get();


        if($existingStudents->isEmpty()){
            if (($handle = fopen(Storage::path($path), 'r')) !== false) {
                fgetcsv($handle); 
                while (($data = fgetcsv($handle)) !== false) {
                    if ($existingStudents->isEmpty()) {
                        Student::create([
                            'name' => "{$data[0]}, {$data[1]}", 
                            'grade' => intval($data[2]),
                            'section' => intval($data[3]),
                            'school_year_id' => $schoolYearRecord->id,
                        ]);
                    }
                }
                fclose($handle);
            }
            return back()->with('success', 'Students added successfully!');
        }
        return back()->with('success', 'Students added successfully!');
    }
    public function profile(Student $student){

        $attendanceRecords = Attendance::where('student_id', $student->id)
        ->orderBy('date', 'asc')
        ->paginate(20);
    
        $attendanceData = $this->calculateAttendanceData($student);
        return view('students.student_profile', array_merge($attendanceData, [
            'student' => $student,
            'attendanceRecords' => $attendanceRecords,
        ]));
    

    }
    public function filterStudentAttendance(Request $request, Student $student){

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);


        // $startYear = Carbon::parse($request->input('start_date'))->format('Y');
        // $endYear = Carbon::parse($request->input('end_date'))->format('Y');

        // $yearRange = "{$startYear}-{$endYear}";
        // //ask maam kung anong day nag eend ang school year
        // if($student->schoolYear->year != $yearRange ){
        //     return redirect()->back()->with('error', 'The selected school year does not match the student\'s school year.');

        // }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $attendanceRecords = Attendance::where('student_id', $student->id)
        ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            return $q->whereBetween('date', [$startDate, $endDate]);
        })
        ->orderBy('date', 'asc')
        ->paginate(20)
        ->appends($request->all());
        
        $attendanceData = $this->calculateAttendanceData($student, $startDate, $endDate);


        return view('students.student_profile', array_merge($attendanceData, [
            'student' => $student,
            'attendanceRecords' => $attendanceRecords
        ]));


    }
    private function calculateAttendanceData($student, $startDate = '', $endDate = ''){

        $attendanceStudent = Attendance::where('student_id', $student->id);

        if($startDate && $endDate){
            $attendanceStudent->whereBetween('date',[$startDate, $endDate]);
        }
        $attendanceRecord = $attendanceStudent->get();

       
        $totalPresentMorning = $attendanceRecord->where('status_morning', 'present')->count();
        $totalPresentAfternoon = $attendanceRecord->where('status_lunch', 'present')->count();
        $totalAbsentMorning = $attendanceRecord->where('status_morning', 'absent')->count();
        $totalAbsentAfternoon = $attendanceRecord->where('status_lunch', 'absent')->count();
        
        $totalAbsent = ($totalAbsentMorning * 0.5) + ($totalAbsentAfternoon * 0.5);
        $totalDays = $attendanceStudent->count();
        
        $attendancePercentageMorning = $totalDays > 0 ? ($totalPresentMorning / $totalDays) * 100 : 0;
        $attendancePercentageAfternoon = $totalDays > 0 ? ($totalPresentAfternoon / $totalDays) * 100 : 0;

        return [
            'totalPresentMorning' => $totalPresentMorning,
            'totalPresentAfternoon' => $totalPresentAfternoon,
            'totalAbsent' => $totalAbsent,
            'attendancePercentageMorning' => $attendancePercentageMorning,
            'attendancePercentageAfternoon' => $attendancePercentageAfternoon,
        ];

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::paginate(11);
        $schoolYears = SchoolYear::all();

        return view('students.students_list', compact('students', 'schoolYears'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rfid_tag' => 'required|string',
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:7|max:10',
            'section' => 'required|integer|min:1|max:3',
        ]);
        $student = Student::create([
            'name' => $request->name,
            'grade' => $request->grade,
            'section' => $request->section,
            'school_year_id' => SchoolYear::where('is_active', true)->first()->id
        ]);
        Tag::create([
            'rfid_tag' => $request->rfid_tag,
            'student_id' => $student->id,
        ]);
        

        return redirect()->route('students.index')->with('success', 'Student added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'rfid_tag' => 'required|string',
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:7|max:10',
            'section' => 'required|integer|min:1|max:3',
        ]);
        $student->update([
            'name' => $request->name,
            'grade' => $request->grade,
            'section' => $request->section,
            'school_year_id' => SchoolYear::where('year', $request->input('school_year'))->first()->id
        ]);
        if ($request->rfid_tag) {
            if ($student->tag) {
                $student->tag->update(['rfid_tag' => $request->rfid_tag]);
            } else {
                Tag::create(['rfid_tag' => $request->rfid_tag,
                                                'student_id' => $student->id]);
            }
        }
        return redirect()->route('students.index')->with('success', 'Student edited successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully!');
    }
}
