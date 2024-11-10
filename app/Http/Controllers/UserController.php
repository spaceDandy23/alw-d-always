<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends Controller
{
    public function listIndex(){


        $studentsGroupedBySection = Auth::user()->students->groupBy(function ($student) {
            return $student->grade . '-' . $student->section;
        })
        ->map(function($students){

            return $students->each(function ($student){


                $attendanceCount = $student->attendances->count();
                $presentCount = $student->attendances
                ->where('status_morning', 'present')
                ->where('status_lunch', 'present')
                ->count();
                $absentCount = $student->attendances
                ->where('status_morning', 'absent')
                ->where('status_lunch', 'absent')
                ->count();


                $averagePresent = $attendanceCount > 0 ? ($presentCount / $attendanceCount) * 100 : 0;


                $averageAbsent = $attendanceCount > 0 ? ($absentCount / $attendanceCount) * 100 : 0;

                $student->average_present = $averagePresent;
                $student->average_absent = $averageAbsent;


                
            });
            

        });


        return view('students.watch_list', compact('studentsGroupedBySection'));
    }
    // public function filterStudent(Request $request){

    //     $name = $request->input('name');
    //     $grade = $request->input('grade');
    //     $section = $request->input('section');
    //     $sanitizedName = preg_replace('/[\s,]+/', ' ', trim($name)); 
    //     $setOfNames = explode(' ', $sanitizedName);
    //     $schoolYear = SchoolYear::where('is_active', true)->first();
    //     $studentQuery = Student::query()
    //     ->when($setOfNames, function($q, $setOfNames){
    //         foreach($setOfNames as $name){
    //             $name = trim($name);
    //             $q->orWhere('name', 'LIKE', "%{$name}%");
    //         }
    //     })
    //     ->when($grade, function($q, $grade){
    //         return $q->where('grade', $grade);
    //     })
    //     ->when($section, function($q, $section){
    //         return $q->where('section', $section);
    //     })
    //     ->when($schoolYear, function($q, $schoolYear){
    //         return $q->where('school_year_id',  $schoolYear->id);
    //     })
    //     ->paginate(20);


    //     return response()->json([
    //         'success' => true,
    //         'results' => $studentQuery,
    //     ]);


    // }

    public function storeWatchlist(Request $request){
        $sections = $request->input('sections', []);

        if(!$sections){
            return back()->with('error', 'Please fill out at least one checkbox');
        }

        foreach($sections as $section){

            $students = Student::where('grade', $section[0])
            ->where('section', $section[2])
            ->where('school_year_id', SchoolYear::where('is_active', true)->first()->id)
            ->pluck('id');
            if ($students->isNotEmpty()) {
                foreach ($students as $id) {
                    if (!Auth::user()->students()->where('student_id', $id)->exists()) {
                        Auth::user()->students()->attach($id, [
                            'created_at' => now(), 
                            'updated_at' => now()
                        ]);
                    }
                }

            }

        }
        
        
        return redirect()->route('list.index')->with('success','Section added successfully');


    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(5); 
        $roles = ['admin','teacher'];
        return view('users.users_list',compact('users','roles')); 
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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,',
            'password'=> 'required|string|min:8',
        ]);
        User::create([
                        'name' => $validatedData['name'],
                        'email' => $validatedData['email'],
                        'password' => Hash::make($validatedData['password']),   
                        'role' => $request->role
                    ]);
        return redirect()->route('users.index')->with('success', 'User added');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);


        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email'=> 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'=> 'required|string|min:8',
        ]);


        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $request->role,
        ]);
        return redirect()->route('users.index')->with('success', 'User Edited');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->route('users.index')->with(['success' => 'User Deleted']);
    }
}
