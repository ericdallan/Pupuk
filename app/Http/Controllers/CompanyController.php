<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function company_page()
    {
        $company = Company::first(); // Mengambil data perusahaan pertama
        return view('company.company_page', compact('company'));
    }
    public function update(Request $request)
    {
        // Validasi data yang masuk
        $request->validate([
            'company_name' => 'required',
            'director' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi logo
        ]);

        $company = Company::firstOrNew(); // Ambil data perusahaan pertama atau buat baru jika tidak ada

        // Proses upload logo (jika ada)
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($company->logo) {
                Storage::delete('public/' . $company->logo);
            }

            $logoPath = $request->file('logo')->store('public/logos');
            $company->logo = str_replace('public/', '', $logoPath); // Simpan path relatif
        }

        // Update data perusahaan
        $company->company_name = $request->input('company_name');
        $company->address = $request->input('address');
        $company->director = $request->input('director');
        $company->phone = $request->input('phone');
        $company->email = $request->input('email');
        $company->save();

        return redirect()->route('company_page')->with('success', 'Data perusahaan berhasil diperbarui.');
    }
}
