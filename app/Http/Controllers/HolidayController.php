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
            'description' => 'nullable|string|max:1000',
            'start_month' => 'required|integer',
            'start_day' => 'required|integer',
            'end_month' => 'nullable|integer',
            'end_day' => 'nullable|integer'
        ]);
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $startDay = $request->input('start_day');
        $endDay = $request->input('end_day');

        $errorMessages = $this->validate($startMonth, $endMonth, $startDay, $endDay);

        if($errorMessages){
            return redirect()->back()->with('error', $errorMessages);
        }

        if ($startMonth === $endMonth && $startDay === $endDay){

            $endMonth = null;
            $endDay = null;
        }

        Holiday::create([
            'name' => $request->name,
            'description' => $request->description,
            'month' => $request->start_month,
            'day' => $request->start_day,
            'end_month' => $endMonth,
            'end_day' => $endDay,
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
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $startDay = $request->input('start_day');
        $endDay = $request->input('end_day');

        $errorMessages = $this->validate($startMonth, $endMonth, $startDay, $endDay);

        if($errorMessages){
            return redirect()->back()->with('error', $errorMessages);
        }



        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_month' => 'required|integer',
            'start_day' => 'required|integer',
            'end_month' => 'nullable|integer',
            'end_day' => 'nullable|integer'
        ]);
        $holiday->update([
            'name' => $request->name,
            'month' => $request->start_month,
            'day' => $request->start_day,
            'description' => $request->description,
            'end_month' => $request->end_month,
            'end_day' => $request->end_day
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
    public function validate($startMonth, $endMonth, $startDay, $endDay){

        $errorMessages = [];

        if ($endMonth && !$endDay) {
            $errorMessages[] = 'The end day must have a value if end month specified.';
        }
    
        if ($endMonth && $endDay) {
            if ($endMonth < $startMonth) {
                $errorMessages[] = 'The end date must be after the start date.';
            } elseif ($endMonth == $startMonth && $endDay < $startDay) {
                $errorMessages[] = 'The end day must be after the start day.';
            }
        }

        if ($errorMessages) {
            return implode(' ', $errorMessages);
        }


    }
}
