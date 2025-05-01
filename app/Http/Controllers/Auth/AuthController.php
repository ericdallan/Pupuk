<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login_page()
    {
        //show login page
        return view('auth.login_page');
    }

    public function login(Request $request)
    {
        // Validate Login
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            session(['login' => true]);
            return redirect()->intended('/account_page')->with('success', 'Admin Login Successful!');
        } else {
            return redirect()->back()->with('failed', 'Admin Email or Password Incorrect!');
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout(); // Logout the 'admin' guard
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login_page')->with('success', 'Logout Successful!');
    }
}
?>