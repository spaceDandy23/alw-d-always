<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    //

    public function login(Request $request)
    {
        if ($request->isMethod('POST')) {
            $validatedData = $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:8', 
            ]);
    
            if (Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']])) {
                if (Auth::user()->isAdmin()) {
                    $request->session()->regenerate();
                    return redirect()->route('dashboard');
                }
                elseif (Auth::user()->isTeacher()) {
                    $request->session()->regenerate();
                    return redirect()->route('teacher.dashboard');
                }
            } else {
                return back()->withErrors(['password' => 'The provided password is incorrect.']);
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
