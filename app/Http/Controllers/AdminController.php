<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Display the admin dashboard page
     *
     * @return \Illuminate\View\View
     */
    public function dashboard_page()
    {
        return view('admin.dashboard_page');
    }

    /**
     * Display the admin profile page
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function admin_profile()
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            return view('admin.admin_profile', compact('admin'));
        }
        return redirect()->route('admin.login')->with('message', 'You need to be logged in to view your profile.');
    }

    /**
     * Update admin profile
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function admin_update(Request $request)
    {
        $user = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            $admin = $this->adminService->updateProfile($request->all(), $user);
            $admin->save();

            DB::commit();
            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating profile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        }
    }
}
