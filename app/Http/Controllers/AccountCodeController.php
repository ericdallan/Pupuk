<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Services\AccountCodeService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccountCodeExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class AccountCodeController extends Controller
{
    protected $accountCodeService;

    public function __construct(AccountCodeService $accountCodeService)
    {
        $this->accountCodeService = $accountCodeService;
    }

    /**
     * Display the account code page with hierarchical data
     *
     * @return \Illuminate\View\View
     */
    public function account_page()
    {
        try {
            $accounts = ChartOfAccount::all();
            $hierarkiAkun = $this->accountCodeService->buildHierarchy($accounts);
            return view('AccountCode.accountCode_page', compact('hierarkiAkun'));
        } catch (\Exception $e) {
            Log::error('Error in account_page: ' . $e->getMessage());
            return Redirect::route('account_page')->with('error', 'Failed to load account page.');
        }
    }

    /**
     * Generate new account code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateAccountCode(Request $request)
    {
        try {
            $accountCode = $this->accountCodeService->generateAccountCode($request->all());
            return response()->json(['account_code' => $accountCode]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error generating account code: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create new account
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create_account(Request $request)
    {
        try {
            $account = $this->accountCodeService->createAccount($request->all());
            return Redirect::route('account_page')
                ->with('success', "Account {$account->account_name} successfully created with code: {$account->account_code}");
        } catch (ValidationException $e) {
            return Redirect::route('account_page')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating account: ' . $e->getMessage());
            return Redirect::route('account_page')
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show edit account form
     *
     * @param string $accountCode
     * @return \Illuminate\View\View
     */
    public function edit_account($accountCode)
    {
        try {
            $account = ChartOfAccount::where('account_code', $accountCode)->firstOrFail();
            return view('AccountCode.accountCode_edit', compact('account'));
        } catch (\Exception $e) {
            Log::error('Error loading edit account page: ' . $e->getMessage());
            return Redirect::route('account_page')->with('error', 'Account not found.');
        }
    }

    /**
     * Update existing account
     *
     * @param Request $request
     * @param string $accountCode
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_account(Request $request, $accountCode)
    {
        try {
            $account = ChartOfAccount::where('account_code', $accountCode)->firstOrFail();

            $request->validate([
                'account_name' => 'required|string|max:255|unique:chart_of_accounts,account_name,' . $account->id,
            ]);

            $account->update([
                'account_name' => $request->input('account_name'),
            ]);

            return Redirect::route('account_page')->with('success', 'Account updated successfully!');
        } catch (ValidationException $e) {
            return Redirect::back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating account: ' . $e->getMessage());
            return Redirect::route('account_page')->with('error', 'Failed to update account.');
        }
    }

    /**
     * Export accounts to Excel
     *
     * @return \Maatwebsite\Excel\Facades\Excel
     */
    public function exportExcel()
    {
        try {
            $accounts = ChartOfAccount::all();
            $hierarkiAkun = $this->accountCodeService->buildHierarchy($accounts);
            return Excel::download(new AccountCodeExport($hierarkiAkun), 'chart_of_accounts.xlsx');
        } catch (\Exception $e) {
            Log::error('Error exporting to Excel: ' . $e->getMessage());
            return Redirect::route('account_page')->with('error', 'Failed to export accounts to Excel.');
        }
    }

    /**
     * Generate PDF of accounts
     *
     * @return \Barryvdh\DomPDF\Facade\Pdf
     */
    public function generatePdf()
    {
        try {
            $accounts = ChartOfAccount::all();
            $hierarkiAkun = $this->accountCodeService->buildHierarchy($accounts);

            $sortedHierarkiAkun = collect($hierarkiAkun)
                ->map(function ($accountSections) {
                    return collect($accountSections)
                        ->map(function ($accountSubsections) {
                            if (is_array($accountSubsections)) {
                                return collect($accountSubsections)
                                    ->map(function ($accountNames) {
                                        if (is_array($accountNames)) {
                                            return collect($accountNames)->sortBy(function ($account) {
                                                return is_array($account) && isset($account[1])
                                                    ? (int) end(explode('.', $account[1]))
                                                    : null;
                                            })->toArray();
                                        }
                                        return $accountNames;
                                    })->toArray();
                            }
                            return $accountSubsections;
                        })->toArray();
                })->toArray();

            $pdf = Pdf::loadView('AccountCode.accountCode_pdf', [
                'hierarkiAkun' => $sortedHierarkiAkun,
            ]);

            return $pdf->download('chart_of_accounts.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return Redirect::route('account_page')->with('error', 'Failed to generate PDF.');
        }
    }
}
