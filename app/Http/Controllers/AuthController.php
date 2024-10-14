<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    //

    public function login(Request $request){
        if($request->isMethod('POST')){
            $validatedData = $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:8',
            ]);
            if (Auth::attempt($validatedData)){
                $request->session()->regenerate();
                return redirect()->route('students.index');
            }

        }
        return view('authentication.login');
    }
    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

}
