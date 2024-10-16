<?php

namespace App\Http\Controllers;

use App\Models\RfidLog;
use App\Models\Tag;
use Illuminate\Http\Request;


class RfidController extends Controller
{
    public function index(){
        $rfidLogs = RfidLog::paginate(3);
        return view('rfid.rfid_logs', compact('rfidLogs'));
    }

    public function verify(Request $request){

        if($request->isMethod('post')){

            

            $studentTag = Tag::where('rfid_tag', $request->input('rfid_tag'))->first();

            if(!$studentTag){


                return response()->json([
                    'success' => false,
                    'message' => 'RFID tag is not registered',
                ]);
            }

            RfidLog::create([

                'student_id' => $studentTag->student->id,
                'check_in_time' => now()->format('H:i:s'),
                'date' => now()->format('Y-m-d')

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'student' => $studentTag->student
            ]);

            
        }

        return view('rfid.rfid_scan');


    }
}
