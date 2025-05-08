<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard_page()
    {
        return view('admin.dashboard_page');
    }
    public function admin_profile()
    {
        // Check if an admin is logged in
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user(); // Get the currently logged-in admin
            return view('admin.admin_profile', compact('admin'));
        } else {
            // Handle the case where no admin is logged in (e.g., redirect to login)
            return redirect()->route('admin.login')->with('message', 'You need to be logged in to view your profile.');
        }
    }
    public function admin_update(Request $request)
    {
        // Get the currently logged-in admin
        $user = Auth::guard('admin')->user();

        // Define validation rules
        $rules = [
            'current_password' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $user->id, // Ensure email is unique, excluding the current user's email
            'new_password' => 'nullable|min:8|confirmed', // 'confirmed' rule checks if new_password_confirmation matches
        ];

        // Define custom error messages
        $messages = [
            'current_password.required' => 'Kata sandi saat ini diperlukan.',
            'name.required' => 'Nama diperlukan.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email diperlukan.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.unique' => 'Email sudah digunakan.',
            'new_password.min' => 'Kata sandi baru harus minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules, $messages);

        // If validation fails, redirect back with errors and input
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Verify the current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        // Use a transaction to ensure data consistency
        DB::beginTransaction();
        try {
            // Update the admin's name and email
            $admin = Admin::find($user->id); // Find the admin model instance to update
            $admin->name = $request->name;
            $admin->email = $request->email;

            // Update the password if a new password is provided
            if ($request->filled('new_password')) {
                $admin->password = Hash::make($request->new_password);
            }

            // Save the changes
            $admin->save();

            // Commit the transaction
            DB::commit();

            // Redirect back with a success message
            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Log the error (optional)
            Log::error('Error updating profile: ' . $e->getMessage());

            // Redirect back with an error message
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.');
        }
    }
}
