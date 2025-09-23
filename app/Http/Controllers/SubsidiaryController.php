<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subsidiary;
use App\Models\Voucher;
use App\Models\VoucherDetails;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubsidiariesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\SubsidiaryService;

class SubsidiaryController extends Controller
{
    protected $subsidiaryService;

    public function __construct(SubsidiaryService $subsidiaryService)
    {
        $this->subsidiaryService = $subsidiaryService;
    }

    /**
     * Display the Subsidiary Utang page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function subsidiaryUtang_page(Request $request)
    {
        try {
            $data = $this->subsidiaryService->prepareUtangData($request->all());
            return view('subsidiary_utang.subsidiaryUtang_page', $data);
        } catch (\Exception $e) {
            Log::error('Subsidiary Utang Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman utang']);
        }
    }

    /**
     * Fetch detailed transactions for Subsidiary Utang
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subsidiaryUtangDetails(Request $request)
    {
        try {
            $filters = $request->query();
            $transactions = $this->subsidiaryService->getTransactions($filters);
            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error in subsidiaryUtangDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * Display the Subsidiary Piutang page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function subsidiaryPiutang_page(Request $request)
    {
        try {
            $data = $this->subsidiaryService->preparePiutangData($request->all());
            return view('subsidiary_utang.subsidiaryPiutang_page', $data);
        } catch (\Exception $e) {
            Log::error('Subsidiary Piutang Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman piutang']);
        }
    }

    /**
     * Fetch detailed transactions for Subsidiary Piutang
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subsidiaryPiutangDetails(Request $request)
    {
        try {
            $filters = $request->query();
            $transactions = $this->subsidiaryService->getTransactions($filters);
            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error in subsidiaryPiutangDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * Generate a subsidiary code
     *
     * @param string $accountCode
     * @return string
     */
    public function generateSubsidiaryCode(string $accountCode): string
    {
        return $this->subsidiaryService->generateSubsidiaryCode($accountCode);
    }

    /**
     * Create a new subsidiary store
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create_store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'account_name' => 'required|string|in:Piutang Usaha,Utang Usaha',
                'store_name' => 'required|string|max:255',
            ]);

            $accountCodesMap = [
                'Piutang Usaha' => '1.1.03.01',
                'Utang Usaha' => '2.1.01.01',
            ];

            $accountCode = $accountCodesMap[$validatedData['account_name']];
            $generateCodeSubsidiary = $this->generateSubsidiaryCode($accountCode);

            $existingSubsidiary = Subsidiary::where('store_name', $validatedData['store_name'])
                ->where('account_code', $accountCode)
                ->first();

            if ($existingSubsidiary) {
                return redirect()->back()->with('error', 'Nama toko sudah terdaftar untuk akun ' . $validatedData['account_name']);
            }

            $fullAccountName = ($validatedData['account_name'] === 'Piutang Usaha')
                ? "Piutang {$validatedData['store_name']}"
                : "Utang {$validatedData['store_name']}";

            $subsidiary = new Subsidiary();
            $subsidiary->subsidiary_code = $generateCodeSubsidiary;
            $subsidiary->account_name = $fullAccountName;
            $subsidiary->account_code = $accountCode;
            $subsidiary->store_name = $validatedData['store_name'];
            $subsidiary->save();

            return redirect()->back()->with('success', 'Buku Besar Pembantu berhasil disimpan untuk akun ' . $validatedData['account_name']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating subsidiary: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Generate PDF for Subsidiary Utang
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function generateUtangPdf(Request $request)
    {
        try {
            $filters = $request->query();
            $data = $this->subsidiaryService->preparePdfData($filters);

            $pdf = Pdf::loadView('subsidiary_utang.subsidiary_utang_pdf', $data);

            $filename = $data['invoice_number'] === 'Semua Invoice'
                ? 'laporan_utang_usaha_semua_invoice.pdf'
                : 'laporan_utang_usaha_invoice_' . $data['invoice_number'] . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating Utang PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF for Subsidiary Piutang
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function generatePiutangPdf(Request $request)
    {
        try {
            $filters = $request->query();
            $data = $this->subsidiaryService->preparePdfData($filters);

            $pdf = Pdf::loadView('subsidiary_utang.subsidiary_piutang_pdf', $data);

            $filename = $data['invoice_number'] === 'Semua Invoice'
                ? 'laporan_piutang_usaha_semua_invoice.pdf'
                : 'laporan_piutang_usaha_invoice_' . $data['invoice_number'] . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating Piutang PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export subsidiary data to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function subsidiaryExcel(Request $request)
    {
        try {
            return Excel::download(new SubsidiariesExport($request), 'laporan_subsidiari.xlsx');
        } catch (\Exception $e) {
            Log::error('Subsidiary Excel Export Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengekspor data subsidiari']);
        }
    }

    /**
     * Update a subsidiary for Piutang
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function piutangUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'store_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255|regex:/^Piutang\s.+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->all();
            $data['subsidiary_id'] = $id; // Ensure ID from route is used
            $this->subsidiaryService->updateSubsidiary($data);
            return response()->json([
                'success' => true,
                'message' => 'Akun pembantu piutang berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating piutang subsidiary: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun pembantu piutang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a subsidiary for Utang
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function utangUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'store_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255|regex:/^Utang\s.+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->all();
            $data['subsidiary_id'] = $id; // Ensure ID from route is used
            $this->subsidiaryService->updateSubsidiary($data);
            return response()->json([
                'success' => true,
                'message' => 'Akun pembantu utang berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating utang subsidiary: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun pembantu utang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a subsidiary
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subsidiaryDelete($id)
    {
        try {
            $this->subsidiaryService->deleteSubsidiary((int) $id);
            return redirect()->back()->with('success', 'Data subsidiary berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting subsidiary: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data subsidiary: ' . $e->getMessage());
        }
    }
}
