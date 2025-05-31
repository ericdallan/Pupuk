<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CompanyService
{
    /**
     * Update or create company data
     *
     * @param array $data
     * @param Request $request
     * @return Company
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateOrCreateCompany(array $data, Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'director' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $company = Company::firstOrNew();

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::delete('public/' . $company->logo);
            }

            $logoPath = $request->file('logo')->store('public/logos');
            $company->logo = str_replace('public/', '', $logoPath);
        }

        $company->company_name = $data['company_name'];
        $company->address = $data['address'];
        $company->director = $data['director'];
        $company->phone = $data['phone'];
        $company->email = $data['email'];

        $company->save();

        return $company;
    }
}
