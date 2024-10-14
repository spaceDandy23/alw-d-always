<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guardians = Guardian::paginate(3);

        return view('guardians.guardians_list', compact('guardians'));
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
