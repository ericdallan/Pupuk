<?php

namespace App\Http\Controllers;

use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Display the voucher page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function voucher_page(Request $request)
    {
        try {
            $data = $this->voucherService->prepareVoucherPageData($request);
            return view('voucher.voucher_page', $data);
        } catch (\Exception $e) {
            Log::error('Voucher Page Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman voucher']);
        }
    }

    /**
     * Create a new voucher
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voucher_form(Request $request)
    {
        $validator = Validator::make(
            array_merge($request->all(), [
                'voucher_details' => collect($request->voucher_details ?? [])
                    ->filter(function ($detail) {
                        return !empty($detail['account_code']);
                    })
                    ->values()
                    ->toArray(),
            ]),
            [
                'voucher_type' => 'required|string|max:255',
                'voucher_date' => 'required|date',
                'voucher_day' => 'nullable|string|max:255',
                'prepared_by' => 'nullable|string|max:255',
                'given_to' => 'nullable|string|max:255',
                'approved_by' => 'nullable|string|max:255',
                'transaction' => 'nullable|string|max:255',
                'due_date' => 'nullable|date',
                'store' => 'nullable|string|max:255',
                'invoice' => 'nullable|string|max:255|required_if:use_invoice,yes',
                'total_debit' => 'required|numeric|min:0',
                'total_credit' => 'required|numeric|min:0',
                'transactions' => 'nullable|array',
                'transactions.*.description' => 'nullable|string|max:255',
                'transactions.*.quantity' => 'nullable|integer|min:1',
                'transactions.*.nominal' => 'nullable|numeric|min:0',
                'voucher_details' => 'required|array|min:1',
                'voucher_details.*.account_code' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($request) {
                        $useInvoice = $request->use_invoice === 'yes';
                        $voucherDetailsData = collect($request->voucher_details ?? [])
                            ->filter(function ($detail) {
                                return !empty($detail['account_code']);
                            })
                            ->values()
                            ->toArray();

                        $hasSubsidiaryCode = collect($voucherDetailsData)->contains(function ($detail) {
                            return \App\Models\Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                        });

                        if ($useInvoice) {
                            if ($hasSubsidiaryCode) {
                                if (
                                    !\App\Models\ChartOfAccount::where('account_code', $value)->exists() &&
                                    !\App\Models\Subsidiary::where('subsidiary_code', $value)->exists()
                                ) {
                                    $fail("Kode akun {$value} tidak valid di tabel chart_of_accounts atau subsidiaries.");
                                }
                            } else {
                                if (!\App\Models\Subsidiary::where('subsidiary_code', $value)->exists()) {
                                    $fail("Kode akun {$value} tidak valid di tabel subsidiaries.");
                                }
                            }
                        } else {
                            if (!\App\Models\ChartOfAccount::where('account_code', $value)->exists()) {
                                $fail("Kode akun {$value} tidak valid di tabel chart_of_accounts.");
                            }
                        }
                    },
                ],
                'voucher_details.*.account_name' => 'nullable|string|max:255',
                'voucher_details.*.debit' => 'nullable|numeric|min:0',
                'voucher_details.*.credit' => 'nullable|numeric|min:0',
            ],
            [
                'voucher_details.required' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
                'voucher_details.min' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
                'voucher_details.*.account_code.required' => 'Kode Akun wajib diisi pada setiap baris Rincian Voucher.',
                'invoice.required_if' => 'Nomor Invoice wajib diisi jika menggunakan invoice.',
                'dueDate.required_if' => 'Tanggal Jatuh Tempo wajib diisi jika menggunakan invoice.',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $voucherNumber = $this->voucherService->generateVoucherNumber($request->voucher_type);
            $this->voucherService->createVoucher($request, $voucherNumber);
            return redirect()->back()->with('success', 'Voucher berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('Error creating voucher or invoice: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat membuat voucher atau invoice: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get invoice details
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_invoice_details(Request $request)
    {
        try {
            $details = $this->voucherService->getInvoiceDetails($request->invoice);
            return response()->json($details);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Display the voucher edit form
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function voucher_edit($id)
    {
        try {
            $data = $this->voucherService->prepareVoucherEditData($id);
            return view('voucher.voucher_edit', $data);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Update an existing voucher
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voucher_update(Request $request, $id)
    {
        $validator = Validator::make(
            array_merge($request->all(), [
                'voucher_details' => collect($request->voucher_details ?? [])
                    ->filter(function ($detail) {
                        return !empty($detail['account_code']);
                    })
                    ->values()
                    ->toArray(),
                'transactions' => collect($request->transactions ?? [])
                    ->filter(function ($transaction) {
                        $hasDescription = !empty($transaction['description']);
                        $isHpp = str_starts_with($transaction['description'], 'HPP ');
                        $hasQuantity = isset($transaction['quantity']) && floatval($transaction['quantity']) > 0;
                        $hasNominal = isset($transaction['nominal']) && floatval($transaction['nominal']) >= 0;
                        return $hasDescription && ($isHpp || ($hasQuantity && $hasNominal));
                    })
                    ->values()
                    ->toArray(),
            ]),
            [
                'voucher_number' => 'required|string|max:255',
                'voucher_type' => 'required|in:PJ,PG,PM,PB,LN',
                'voucher_date' => 'required|date',
                'voucher_day' => 'nullable|string|max:50',
                'prepared_by' => 'required|string|max:255',
                'given_to' => 'nullable|string|max:255',
                'approved_by' => 'nullable|string|max:255',
                'transaction' => 'nullable|string|max:255',
                'use_invoice' => 'required|in:yes,no',
                'use_existing_invoice' => 'nullable|in:yes,no',
                'invoice' => [
                    'nullable',
                    'string',
                    'max:255',
                    'required_if:use_invoice,yes',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->use_invoice === 'yes' && $request->use_existing_invoice !== 'yes') {
                            if (\App\Models\Invoice::where('invoice', $value)->exists()) {
                                $fail('Nomor Invoice sudah digunakan. Silakan pilih nomor lain atau gunakan invoice yang sudah ada.');
                            }
                        }
                    },
                ],
                'store' => 'nullable|string|max:255',
                'due_date' => 'nullable|date',
                'transactions' => 'required|array|min:1',
                'transactions.*.description' => 'required|string|max:255',
                'transactions.*.quantity' => 'required|numeric|min:1',
                'transactions.*.nominal' => 'required|numeric|min:0',
                'voucher_details' => 'required|array|min:1',
                'voucher_details.*.account_code' => [
                    'required',
                    'string',
                    'max:50',
                    function ($attribute, $value, $fail) use ($request) {
                        $useInvoice = $request->use_invoice === 'yes';
                        $voucherDetailsData = collect($request->voucher_details ?? [])
                            ->filter(function ($detail) {
                                return !empty($detail['account_code']);
                            })
                            ->values()
                            ->toArray();

                        $hasSubsidiaryCode = collect($voucherDetailsData)->contains(function ($detail) {
                            return \App\Models\Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                        });

                        if ($useInvoice && !$hasSubsidiaryCode) {
                            if (!\App\Models\Subsidiary::where('subsidiary_code', $value)->exists()) {
                                $fail("Kode akun {$value} tidak valid di tabel subsidiaries.");
                            }
                        } else {
                            if (
                                !\App\Models\ChartOfAccount::where('account_code', $value)->exists() &&
                                !\App\Models\Subsidiary::where('subsidiary_code', $value)->exists()
                            ) {
                                $fail("Kode akun {$value} tidak valid di tabel chart_of_accounts atau subsidiaries.");
                            }
                        }
                    },
                ],
                'voucher_details.*.account_name' => 'required|string|max:255',
                'voucher_details.*.debit' => 'nullable|numeric|min:0',
                'voucher_details.*.credit' => 'nullable|numeric|min:0',
            ],
            [
                'voucher_details.required' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
                'voucher_details.min' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
                'voucher_details.*.account_code.required' => 'Kode Akun wajib diisi pada setiap baris Rincian Voucher.',
                'invoice.required_if' => 'Nomor Invoice wajib diisi jika menggunakan invoice.',
                'due_date.required_if' => 'Tanggal Jatuh Tempo wajib diisi untuk invoice baru.',
                'transactions.required' => 'Rincian Transaksi minimal harus memiliki satu baris.',
                'transactions.min' => 'Rincian Transaksi minimal harus memiliki satu baris.',
                'transactions.*.description.required' => 'Deskripsi wajib diisi untuk setiap transaksi.',
                'transactions.*.quantity.required' => 'Kuantitas wajib diisi dan harus lebih dari 0.',
                'transactions.*.quantity.min' => 'Kuantitas harus lebih dari 0.',
                'transactions.*.nominal.required' => 'Nominal wajib diisi untuk setiap transaksi.',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->voucherService->updateVoucher($request, $id);
            return redirect()->back()->with('success', 'Voucher berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Voucher update failed: ' . $e->getMessage());
            return redirect()->back()->with('message', 'Gagal memperbarui voucher: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a voucher
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voucher_delete($id)
    {
        try {
            $this->voucherService->deleteVoucher($id);
            return redirect()->route('voucher_page')->with('success', 'Voucher dan semua data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting voucher', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->withErrors(['message' => 'Gagal menghapus voucher: ' . $e->getMessage()]);
        }
    }

    /**
     * Display voucher details
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function voucher_detail($id)
    {
        try {
            $data = $this->voucherService->prepareVoucherDetailData($id);
            return view('voucher.voucher_detail', $data);
        } catch (\Exception $e) {
            Log::error('Voucher Detail Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memuat detail voucher']);
        }
    }

    /**
     * Generate PDF for a voucher
     *
     * @param int $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function generatePdf($id)
    {
        try {
            $data = $this->voucherService->preparePdfData($id);
            $pdf = Pdf::loadView('voucher.voucher_pdf', $data);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->download('voucher-' . $data['voucher']->voucher_number . '.pdf');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Company data not found when trying to generate PDF for voucher ID: {$id}");
            return redirect()->back()->with('error', 'Tidak dapat membuat PDF karena data perusahaan tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error("An unexpected error occurred while generating PDF for voucher ID: {$id}. Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pembuatan PDF.');
        }
    }
}
