<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Services\MasterService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterController extends Controller
{
    protected $MasterService;

    public function __construct(MasterService $MasterService)
    {
        $this->MasterService = $MasterService;
    }

    /**
     * Display the master dashboard page
     *
     * @return \Illuminate\View\View
     */
    public function dashboard_page()
    {
        return view('master.dashboard_page');
    }

    /**
     * Display the master profile page
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function master_profile()
    {
        if (Auth::guard('master')->check()) {
            $master = Auth::guard('master')->user();
            return view('master.master_profile', compact('master'));
        }
        return redirect()->route('master.login')->with('message', 'You need to be logged in to view your profile.');
    }

    /**
     * Update master profile
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function master_update(Request $request)
    {
        if (!Auth::guard('master')->check()) {
            return redirect()->route('master.login')->with('error', 'You need to be logged in to update your profile.');
        }

        $user = Auth::guard('master')->user();

        // Ensure $user is an instance of Master
        if (!$user instanceof Master) {
            Log::error('Authenticated user is not an instance of Master.');
            return redirect()->back()->with('error', 'Invalid user type.');
        }

        DB::beginTransaction();
        try {
            $master = $this->MasterService->updateProfile($request->all(), $user);
            $master->save();

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
