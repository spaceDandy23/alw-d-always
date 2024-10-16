<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;


class NotificationController extends Controller
{
    //

    public function index(){

        $notifications = Notification::paginate(5);
        return view('notifications.notifications_list', compact('notifications'));
    }
}
