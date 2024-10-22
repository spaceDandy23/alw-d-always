<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $holidays = Holiday::paginate(30);


        return view('attendances.holidays', compact('holidays'));
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'month' => 'required',
            'day' => 'required'
        ]);


        
        Holiday::create([
            'name' => $request->name,
            'description' => $request->description,
            'month' => $request->month,
            'day' => $request->day,
        ]);
    
        return redirect()->route('holidays.index')->with('success', 'Holiday added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000', 
        ]);
        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
        ]);
        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully!');
    }
}
