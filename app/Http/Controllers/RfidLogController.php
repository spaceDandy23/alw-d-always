<?php

namespace App\Http\Controllers;

use App\Models\RfidLog;


class RfidLogController extends Controller
{
    public function index(){
        $rfidLogs = RfidLog::paginate(3);
        return view('rfid_logs.rfid_logs', compact('rfidLogs'));
    }
}
