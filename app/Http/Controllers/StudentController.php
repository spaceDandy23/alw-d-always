<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\Tag;
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

            
            return redirect()->route('register')->with('success', 'Student Registered');


        }
        return view('student.register_student');

    }
    public function search(Request $request){

        $query = $request->input('search');

        $columns = Schema::getColumnListing('students');
        $studentQuery = Student::query();
        if(ctype_digit($query)){
            $integerQuery = intVal($query);
            $studentQuery->where('grade', 'LIKE', $integerQuery)
            ->orWhere('section', 'LIKE', $integerQuery);
        }
        else{
            foreach($columns as $column){
                $studentQuery->orWhere($column, 'LIKE', "%{$query}%");
            }
        }

        if($studentQuery->get()->isEmpty()){
            return response()->json(['success' => false]);
        }


        return response()->json([
            'success' => true,
            'results' => $studentQuery->paginate(5),
        ]);



    }
    public function importCSV(Request $request)
    {
        $directory = 'public/csv';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);  
        }
        
        $file = $request->file('csv_file');
        $path = $file->storeAs('public/csv', $file->getClientOriginalName());
        if (($handle = fopen(Storage::path($path), 'r')) !== false) {
            fgetcsv($handle); 
            while (($data = fgetcsv($handle)) !== false) {
                Student::create([
                    'name' => "{$data[0]}, {$data[1]}", 
                    'grade' => intval($data[2]), 
                    'section' => intval($data[3]), 
                ]);
            }
            fclose($handle);
        }
        
        return back()->with('success', 'Students added successfully!');
        
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::paginate(3);

        return view('student.students_list', compact('students'));

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
            'rfid_tag' => 'required|string|unique:tags,rfid_tag',
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:7|max:10',
            'section' => 'required|integer|min:1|max:3',
        ]);

        $student = Student::create([
            'name' => $request->name,
            'grade' => $request->grade,
            'section' => $request->section,
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
            'rfid_tag' => 'required|string|unique:tags,rfid_tag,' . ($student->tag ? $student->tag->id : ''),
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:7|max:10',
            'section' => 'required|integer|min:1|max:3',
        ]);
        $student->update([
            'name' => $request->name,
            'grade' => $request->grade,
            'section' => $request->section,
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
