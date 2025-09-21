<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show admin login page
     *
     * @return \Illuminate\View\View
     */
    public function admin_login()
    {
        return view('auth.login_page');
    }

    /**
     * Handle login form submission - checks both admin and master
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Try admin authentication first
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return Redirect::intended(route('dashboard_page'));
        }

        // If admin auth fails, try master authentication
        if (Auth::guard('master')->attempt($credentials)) {
            $request->session()->regenerate();
            return Redirect::intended(route('dashboard_page'));
        }

        // Both authentications failed
        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Show master login page
     *
     * @return \Illuminate\View\View
     */
    public function master_login_page()
    {
        return view('auth.master_login');
    }

    /**
     * Handle master login form submission
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function master_login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('master')->attempt($credentials)) {
            $request->session()->regenerate();
            return Redirect::intended(route('dashboard_page'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Show general login page (redirects to admin login)
     *
     * @return \Illuminate\View\View
     */
    public function login_page()
    {
        return $this->admin_login();
    }

    /**
     * Handle logout for both admin and master
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('master')->check()) {
            Auth::guard('master')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login');
    }

    /**
     * Check if current user is admin
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return Auth::guard('admin')->check();
    }

    /**
     * Check if current user is master
     *
     * @return bool
     */
    public static function isMaster()
    {
        return Auth::guard('master')->check();
    }

    /**
     * Get current user type
     *
     * @return string|null
     */
    public static function getUserType()
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        } elseif (Auth::guard('master')->check()) {
            return 'master';
        }
        return null;
    }
}
