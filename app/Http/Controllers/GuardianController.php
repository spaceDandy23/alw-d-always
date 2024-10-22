<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{

    public function search(Request $request){

        $guardianName = $request->input('guardian_name');
        $phoneNumber = $request->input('phone_number');
        $relationship = $request->input('relationship');

        $guardians = Guardian::when($guardianName, function ($q, $guardianName){
            return $q->where('name', 'LIKE', "%{$guardianName}%");
        })
        ->when($phoneNumber, function($q, $phoneNumber){
            return $q->where('contact_info', 'LIKE', "%{$phoneNumber}%");
        })
        ->when($relationship, function($q, $relationship){
            return $q->where('relationship_to_student', $relationship);
        })
        ->paginate(5)
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
        $guardians = Guardian::paginate(20);


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
        $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:50', 
            'phone_number' => 'nullable|string|max:15',
        ]);

        Guardian::create([
            'name' => $request->name,
            'relationship_to_student' => $request->relationship,
            'contact_info' => $request->phone_number,
        ]);

        return redirect()->route('guardians.index')->with('success', 'Guardian added successfully!');
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
            'relationship' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:15',
        ]);

        $guardian->update([
            'name' => $request->name,
            'relationship_to_student' => $request->relationship,
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
