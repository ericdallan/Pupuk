<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display the company page
     *
     * @return \Illuminate\View\View
     */
    public function company_page()
    {
        try {
            $company = Company::first();
            return view('company.company_page', compact('company'));
        } catch (\Exception $e) {
            Log::error('Error loading company page: ' . $e->getMessage());
            return redirect()->route('company_page')->with('error', 'Gagal memuat halaman perusahaan.');
        }
    }

    /**
     * Update company data
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            $this->companyService->updateOrCreateCompany($request->all(), $request);
            return redirect()->route('company_page')->with('success', 'Data perusahaan berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('company_page')->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating company data: ' . $e->getMessage());
            return redirect()->route('company_page')->with('error', 'Gagal memperbarui data perusahaan. Silakan coba lagi.');
        }
    }
}
