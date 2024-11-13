<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Guardian;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Tag;

use App\Models\TagHistory;
use Illuminate\Http\Request;
use Storage;
use Illuminate\Support\Facades\DB;


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
                
                // 'guardian_first_name' => 'required|string|max:255', 
                // 'guardian_last_name' => 'required|string|max:255', 
                // 'relationship' => 'required|string|max:50', 
                // 'phone_number' => 'nullable|string|max:15',
            ]);
            $student = Student::findOrFail($request->student_id);

            if ($student->tag_id) {
                return back()->with('error', 'The student already has an RFID tag assigned.');
            }
            $tag = Tag::create([
                'rfid_tag' => $request->rfid_tag,
            ]);

            $student->update([
                'tag_id' => $tag->id
            ]);

            // $guardian = Guardian::create([
            //     'name' => "{$request->guardian_last_name}, {$request->guardian_first_name}",
            //     'relationship_to_student' => $request->input('relationship'),
            //     'contact_info' => $request->input('phone_number'),
            // ]);

            // $student->update([
            //     'guardian_id' => $guardian->id
            // ]);

            
            return redirect()->route('register.student.parent')->with('success', 'Student Registered');


        }
        
        $relationships = [
            'Mother',
            'Father',
            'Grandparent',
            'Aunt',
            'Uncle',
            'Sibling',
            'Other'
        ];
        return view('students.register_student', compact('relationships'));

    }
    public function searchQuery($name, $grade, $section){
        $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
        $setOfNames = explode(' ', $sanitizedName);
        $schoolYear = SchoolYear::where('is_active', true)->first();
        return Student::query()
        ->when($setOfNames, function($q, $setOfNames){
            foreach($setOfNames as $name){
                $name = trim($name);
                $q->orWhere('name', 'LIKE', "%{$name}%");
            }
        })
        ->when($grade, function($q, $grade){
            return $q->where('grade', $grade);
        })
        ->when($section, function($q, $section){
            return $q->where('section', $section);
        })
        ->when($schoolYear, function($q, $schoolYear){
            return $q->where('school_year_id',  $schoolYear->id);
        });



    }
    public function search(Request $request){



        if($request->input('fromRegister')){
            $name = $request->input('name');
            $grade = $request->input('grade');
            $section = $request->input('section');


        
            $studentQuery = $this->searchQuery($name, $grade, $section)->paginate(5);


            return response()->json([
                'success' => true,
                'results' => $studentQuery,
            ]);
        }

        if($request->isMethod('get')){
            $name = $request->input('name');
            $grade = $request->input('grade');
            $section = $request->input('section');


            $relationships = [
                'Mother',
                'Father',
                'Grandparent',
                'Aunt',
                'Uncle',
                'Sibling',
                'Other'
            ];
            

            $students = $this->searchQuery($name, $grade, $section)->paginate(10);
            $students->appends($request->all());

            return view('students.students_list', compact('students', 'relationships'));

        }




    }
    public function importCSV(Request $request)
    {

        $request->validate([
            'csv_file' => 'required|file',
            'start_year' => 'required'
        ]);

        SchoolYear::where('is_active', true)->update(['is_active' => false]);

        $directory = 'public/csv';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);  
        }
        $startYear = $request->input('start_year');
        $endYear = $request->input('end_year');

        $schoolYearRecord = SchoolYear::updateOrCreate(
            ['year' => "{$startYear} - {$endYear}"],
            ['is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()]
        
        );



        $file = $request->file('csv_file');
        $csvFile = fopen($file, 'r');


        $header = fgetcsv($csvFile);
    
        DB::transaction(function() use ($csvFile, $header, $schoolYearRecord) {
            while (($row = fgetcsv($csvFile)) !== false) {
                $data = array_combine($header, $row);
                $student = Student::firstOrCreate(
                    [
                        'name' => "{$data['last name']}, {$data['first name']} {$data['middle name']}",
                        'grade' => $data['grade'],
                        'section' => $data['section'],
                        'school_year_id' => $schoolYearRecord->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $guardian = Guardian::firstOrCreate(
                    [
                        'name' => $data['guardian name'],
                        'contact_info' => $data['phone number']
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
    

                $student->guardians()->syncWithoutDetaching([
                    $guardian->id => [
                        'relationship_to_student' => $data['relationship to student'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
            }
        });
    

        fclose($csvFile);
    

        return redirect()->route('students.index')->with('success', 'Students Uploaded Successfully');

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
        
        $attendancePercentageMorning = $totalDays > 0 ? round((($totalPresentMorning / $totalDays) * 100),2) : 0;
        $attendancePercentageAfternoon = $totalDays > 0 ? round((($totalPresentAfternoon / $totalDays) * 100),2) : 0;

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
        $students = Student::with('guardians')->where('school_year_id', SchoolYear::where('is_active', true)->first()->id ?? '')
        ->paginate(11);

        $relationships = [
            'Mother',
            'Father',
            'Grandparent',
            'Aunt',
            'Uncle',
            'Sibling',
            'Other'
        ];
        return view('students.students_list', compact('students',  'relationships'));

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
        // $request->validate([
        //     'last_name' => 'required|string|max:255',
        //     'first_name' => 'required|string|max:255',
        //     'middle_name' => 'required|string|max:255',
        //     'grade' => 'required',
        //     'section' => 'required',
        //     'guardian_last_name' => 'required|string|max:255',
        //     'guardian_first_name' => 'required|string|max:255',
        //     'relationship' => 'required',
        //     'phone_number' => 'required',
        // ]);


        // $guardian = Guardian::create([
        //     'name' => "{$request->guardian_last_name}, {$request->guardian_first_name}",
        //     'relationship_to_student' => $request->relationship,
        //     'contact_info' => $request->phone_number,
        // ]);


        // Student::create([
        //     'name' => "{$request->last_name}, {$request->first_name} {$request->middle_name}",
        //     'grade' => $request->grade,
        //     'section' => $request->section,
        //     'school_year_id' => SchoolYear::where('is_active', true)->first()->id,
        //     'guardian_id' => $guardian->id,
        // ]);



        

        // return redirect()->route('students.index')->with('success', 'Student added successfully!');
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
            'name' => 'required|string|max:255',
            'grade' => 'required|integer',
            'section' => 'required|integer',
        ]);


        if($student->tag){
            TagHistory::create(['student_id' => $student->id, 'rfid_id' => $student->tag->id]);
            $tag = Tag::updateOrCreate(['rfid_tag' => $request->rfid_tag], ['rfid_tag' => $request->rfid_tag]);
            $student->update([
                'tag_id' => $tag->id
            ]);
        }
        $student->update([
            'name' => $request->name,
            'grade' => $request->grade,
            'section' => $request->section,
        ]);


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
