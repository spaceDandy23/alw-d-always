<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\SchoolYear;
use Auth;
use Illuminate\Http\Request;
use Session;

class GuardianController extends Controller
{

    public function search(Request $request){

        $guardianName = $request->input('guardian_name');
        $phoneNumber = $request->input('phone_number');

        $activeSchoolYear = Session::get(Auth::id()) ?? SchoolYear::where('is_active', true)->first();
        // dd($activeSchoolYear->toArray());
        $guardians = Guardian::whereHas('students', function($query) use($activeSchoolYear){
            return $query->when($activeSchoolYear, function($q, $activeSchoolYear){
                return $q->where('school_year_id', $activeSchoolYear->id);
            });
        })
        ->when($guardianName, function ($q, $guardianName){
            return $q->where('name', 'LIKE', "%{$guardianName}%");
        })
        ->when($phoneNumber, function($q, $phoneNumber){
            return $q->where('contact_info', 'LIKE', "%{$phoneNumber}%");
        })
        ->paginate(30)
        ->appends($request->all());
       


        $relationships = [
            'Mother',
            'Father',
            'Grandparent',
            'Aunt',
            'Uncle',
            'Sibling',
            'Other'
        ];
        return view('guardians.guardians_list', compact('guardians', 'relationships'));


    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $activeSchoolYear = Session::get(Auth::id()) ?? SchoolYear::where('is_active', true)->first();


        $guardians = Guardian::whereHas('students', function ($query) use($activeSchoolYear) {
            return $query->when($activeSchoolYear, function ($q) use ($activeSchoolYear){
                return $q->where('school_year_id', $activeSchoolYear->id);
            });
            
        })
        ->with('students')

        // dd($guardians->get()->toArray());

        ->paginate(20);




        $relationships = [
            'Mother',
            'Father',
            'Grandparent',
            'Aunt',
            'Uncle',
            'Sibling',
            'Other'
        ];
        return view('guardians.guardians_list', compact('guardians', 'relationships'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Guardian $guardian)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guardian $guardian)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guardian $guardian)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:15',
        ]);

        $guardian->update([
            'name' => $request->name,
            'contact_info' => $request->phone_number,

        ]);

        return redirect()->route('guardians.index')->with('success', 'Guardian updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guardian $guardian)
    {
        $guardian->delete();

        return redirect()->route('guardians.index')->with('success', 'Guardian deleted successfully!');
    }
}
