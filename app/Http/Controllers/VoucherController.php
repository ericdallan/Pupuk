<?php

namespace App\Http\Controllers;

use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use App\Models\Stock;
use App\Models\TransferStock;
use App\Models\UsedStock;
use App\Models\Transactions;
use App\Models\Invoice;
use App\Models\ChartOfAccount;
use App\Models\Recipes;
use App\Models\Subsidiary;
use Illuminate\Support\Arr;

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
            $data['stocks'] = Stock::select(['item', 'size', 'quantity'])->get();
            $data['transferStocks'] = TransferStock::select(['item', 'size', 'quantity'])->get();
            $data['usedStocks'] = UsedStock::select(['item', 'size', 'quantity'])->get();
            // Combine stocks for PJ vouchers
            $data['pjStocks'] = collect($data['usedStocks'])
                ->map(function ($stock) {
                    return ['item' => $stock->item, 'size' => $stock->size, 'quantity' => $stock->quantity, 'source' => 'used_stocks'];
                })
                ->merge(
                    collect($data['transferStocks'])
                        ->map(function ($stock) {
                            return ['item' => $stock->item, 'size' => $stock->size, 'quantity' => $stock->quantity, 'source' => 'transfer_stocks'];
                        })
                );
            // Use transactionsData from prepareVoucherPageData
            $data['transactions'] = $data['transactionsData']; // Ensure transactions is passed
            $data['recipes'] = Recipes::select(['id', 'product_name', 'size', 'nominal'])->get();
            return view('voucher.voucher_page', $data);
        } catch (\Exception $e) {
            Log::error('Voucher Page Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman voucher']);
        }
    }
    /**
     * Filter and normalize transactions data
     *
     * @param array|null $transactions
     * @return array
     */
    protected function filterTransactions($transactions)
    {
        if (!is_array($transactions)) {
            Log::warning('Invalid transactions array', ['transactions' => $transactions]);
            return [];
        }

        return collect($transactions)
            ->filter(function ($transaction) {
                // Skip transactions without description
                if (!isset($transaction['description']) || empty($transaction['description'])) {
                    return false;
                }
                $description = $transaction['description'];
                $isHpp = str_starts_with($description, 'HPP ');
                $nominal = $transaction['nominal'] ?? $transaction['total'] ?? 0;
                $hasQuantity = isset($transaction['quantity']) && floatval($transaction['quantity']) >= 0.01;
                $hasNominal = floatval($nominal) >= 0;
                return $isHpp || ($hasQuantity && $hasNominal);
            })
            ->values()
            ->toArray();
    }

    /**
     * Filter and normalize voucher details data
     *
     * @param array|null $voucherDetails
     * @return array
     */
    protected function filterVoucherDetails($voucherDetails)
    {
        if (!is_array($voucherDetails)) {
            return [];
        }

        return collect($voucherDetails)
            ->filter(function ($detail) {
                return !empty($detail['account_code']);
            })
            ->values()
            ->toArray();
    }

    /**
     * Validate voucher request data
     *
     * @param Request $request
     * @param array $transactions
     * @param array $voucherDetails
     * @param bool $isUpdate
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateVoucherRequest(Request $request, array $transactions, array $voucherDetails, bool $isUpdate = false)
    {
        $rules = [
            'voucher_type' => 'required|string|in:PJ,PG,PM,PB,LN,PH,PK',
            'voucher_date' => 'required|date',
            'voucher_day' => 'nullable|string|max:50',
            'prepared_by' => 'nullable|string|max:255',
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
                function ($attribute, $value, $fail) use ($request, $isUpdate) {
                    if ($request->use_invoice === 'yes' && $request->use_existing_invoice !== 'yes' && !$isUpdate) {
                        if (Invoice::where('invoice', $value)->exists()) {
                            $fail('Nomor Invoice sudah digunakan. Silakan pilih nomor lain atau gunakan invoice yang sudah ada.');
                        }
                    }
                },
            ],
            'store' => 'nullable|string|max:255',
            'total_debit' => 'required|numeric|min:0',
            'total_credit' => 'required|numeric|min:0',
            'transactions' => 'required_if:voucher_type,PB,PK,PH,PJ|array|min:1',
            'transactions.*.description' => 'required|string|max:255',
            'transactions.*.size' => [
                'nullable', // Make size optional
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->use_stock === 'yes' && in_array($request->voucher_type, ['PB', 'PJ', 'PH', 'PK']) && empty($value)) {
                        $fail('Ukuran wajib diisi ketika menggunakan stok untuk tipe voucher PB, PJ, PH, atau PK.');
                    }
                },
            ],
            'transactions.*.quantity' => 'required|numeric|min:0.01',
            'transactions.*.nominal' => 'required|numeric|min:0',
            'voucher_details' => 'required|array|min:1',
            'voucher_details.*.account_code' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (
                        !ChartOfAccount::where('account_code', $value)->exists() &&
                        !Subsidiary::where('subsidiary_code', $value)->exists()
                    ) {
                        $fail("Kode akun {$value} tidak valid di tabel chart_of_accounts atau subsidiaries.");
                    }
                },
            ],
            'voucher_details.*.account_name' => 'nullable|string|max:255',
            'voucher_details.*.debit' => 'nullable|numeric|min:0',
            'voucher_details.*.credit' => 'nullable|numeric|min:0',
        ];

        if ($isUpdate) {
            $rules['voucher_number'] = 'required|string|max:255';
            $rules['prepared_by'] = 'required|string|max:255';
            $rules['voucher_details.*.account_name'] = 'required|string|max:255';
        }

        // Validate single subsidiary code if use_invoice is yes
        if ($request->use_invoice === 'yes') {
            $rules['voucher_details'] = [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    $subsidiaryCodes = collect($value)->pluck('account_code')
                        ->filter(function ($code) {
                            return Subsidiary::where('subsidiary_code', $code)->exists();
                        });
                    if ($subsidiaryCodes->count() > 1) {
                        $fail('Hanya satu kode subsidiary yang boleh digunakan ketika menggunakan invoice.');
                    }
                },
            ];
        }

        $messages = [
            'voucher_details.required' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
            'voucher_details.min' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
            'voucher_details.*.account_code.required' => 'Kode Akun wajib diisi pada setiap baris Rincian Voucher.',
            'invoice.required_if' => 'Nomor Invoice wajib diisi jika menggunakan invoice.',
            'transactions.required_if' => 'Rincian Transaksi diperlukan untuk voucher tipe PB, PK, PH, atau PJ.',
            'transactions.min' => 'Rincian Transaksi minimal harus memiliki satu baris.',
            'transactions.*.description.required' => 'Deskripsi wajib diisi untuk setiap transaksi.',
            'transactions.*.quantity.required' => 'Kuantitas wajib diisi dan harus lebih dari atau sama dengan 0.01.',
            'transactions.*.quantity.min' => 'Kuantitas minimal adalah 0.01.',
            'transactions.*.nominal.required' => 'Nominal wajib diisi untuk setiap transaksi.',
        ];

        return Validator::make(
            array_merge($request->all(), [
                'transactions' => $transactions,
                'voucher_details' => $voucherDetails,
            ]),
            $rules,
            $messages
        );
    }

    /**
     * Validate stock availability and HPP for transactions
     *
     * @param Request $request
     * @param array $transactions
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    protected function validateStockAndHpp(Request $request, array $transactions, $validator)
    {
        if (!in_array($request->voucher_type, ['PH', 'PK', 'PJ'])) {
            return;
        }

        $nonHppItems = [];
        foreach ($transactions as $index => $transaction) {
            if (!isset($transaction['description']) || empty($transaction['description'])) {
                $validator->errors()->add(
                    "transactions.{$index}.description",
                    "Deskripsi wajib diisi untuk transaksi ke-{$index}."
                );
                continue;
            }
            $item = $transaction['description'];
            $quantity = floatval($transaction['quantity']);
            $size = $transaction['size'] ?? null;
            $errorField = "transactions.{$index}.quantity";

            if (!str_starts_with($item, 'HPP ')) {
                $nonHppItems[$item] = [
                    'index' => $index,
                    'quantity' => $quantity,
                    'size' => $size,
                ];

                if ($request->voucher_type === 'PH') {
                    $stock = Stock::where('item', $item)->where('size', $size)->first();
                    if (!$stock || $stock->quantity < $quantity) {
                        $validator->errors()->add(
                            $errorField,
                            "Stok untuk item {$item} (Ukuran: {$size}) tidak mencukupi di tabel stocks. Tersedia: " . ($stock ? $stock->quantity : 0) . ", Dibutuhkan: {$quantity}."
                        );
                    }
                } elseif ($request->voucher_type === 'PK') {
                    $transferStock = TransferStock::where('item', $item)->where('size', $size)->first();
                    if (!$transferStock || $transferStock->quantity < $quantity) {
                        $validator->errors()->add(
                            $errorField,
                            "Stok untuk item {$item} (Ukuran: {$size}) tidak mencukupi di tabel transfer_stocks. Tersedia: " . ($transferStock ? $transferStock->quantity : 0) . ", Dibutuhkan: {$quantity}."
                        );
                    }
                } elseif ($request->voucher_type === 'PJ') {
                    $usedStock = UsedStock::where('item', $item)->where('size', $size)->first();
                    $stocks = Stock::where('item', $item)->where('size', $size)->first();
                    $totalQuantity = ($usedStock ? $usedStock->quantity : 0) + ($stocks ? $stocks->quantity : 0);
                    if ($totalQuantity < $quantity) {
                        $validator->errors()->add(
                            $errorField,
                            "Stok untuk item {$item} (Ukuran: {$size}) tidak mencukupi di tabel used_stocks atau transfer_stocks. Tersedia: {$totalQuantity}, Dibutuhkan: {$quantity}."
                        );
                    }
                }
            }
        }

        if ($request->voucher_type === 'PJ') {
            foreach ($nonHppItems as $item => $data) {
                $hppItem = "HPP {$item}";
                $hppFound = false;
                $index = $data['index'];
                $expectedQuantity = $data['quantity'];
                $expectedSize = $data['size'];

                foreach ($transactions as $tIndex => $transaction) {
                    if ($transaction['description'] === $hppItem && $transaction['size'] === $expectedSize) {
                        $hppFound = true;
                        $hppQuantity = floatval($transaction['quantity']);
                        $hppNominal = floatval($transaction['nominal']);
                        if ($hppQuantity !== $expectedQuantity) {
                            $validator->errors()->add(
                                "transactions.{$tIndex}.quantity",
                                "Kuantitas untuk HPP {$item} (Ukuran: {$expectedSize}) harus sama dengan kuantitas item utama ({$expectedQuantity})."
                            );
                        }
                        $averageHpp = $this->calculateAverageHpp($item, $expectedSize); // Pass size
                        if (abs($hppNominal - $averageHpp) > 0.01) {
                            $validator->errors()->add(
                                "transactions.{$tIndex}.nominal",
                                "Nominal untuk HPP {$item} (Ukuran: {$expectedSize}) harus sesuai dengan rata-rata HPP ({$averageHpp}). Diterima: {$hppNominal}."
                            );
                        }
                        break;
                    }
                }

                if (!$hppFound) {
                    $validator->errors()->add(
                        "transactions.{$index}.description",
                        "Item {$item} (Ukuran: {$expectedSize}) membutuhkan baris HPP terkait (HPP {$item})."
                    );
                }
            }
        }
    }

    /**
     * Calculate average HPP for an item based on PB transactions
     *
     * @param string $item
     * @return float
     */
    protected function calculateAverageHpp($item, $size = null)
    {
        $baseItem = trim(str_replace('HPP ', '', $item));

        // Join transactions with vouchers to filter by voucher_type
        $transactions = Transactions::join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->whereIn('vouchers.voucher_type', ['PB', 'PK'])
            ->where('transactions.description', $baseItem)
            ->when($size, function ($query, $size) {
                return $query->where('transactions.size', $size);
            })
            ->pluck('transactions.nominal');

        if ($transactions->isEmpty()) {
            Log::warning("No PB or PK transactions found for item: {$baseItem}, size: {$size}");
            return 0;
        }

        return round($transactions->average()) ?: 0;
    }

    /**
     * Create a new voucher
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voucher_form(Request $request)
    {
        $transactions = $this->filterTransactions($request->transactions);
        $voucherDetails = $this->filterVoucherDetails($request->voucher_details);

        $validator = $this->validateVoucherRequest($request, $transactions, $voucherDetails);
        $this->validateStockAndHpp($request, $transactions, $validator);

        if ($validator->fails()) {
            Log::warning('Voucher creation validation failed', [
                'user_id' => auth()->id(),
                'input' => $request->except(['password']),
                'errors' => $validator->errors()->toArray(),
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $voucherNumber = $this->voucherService->generateVoucherNumber($request->voucher_type);
            $this->voucherService->createVoucher($request, $voucherNumber);
            return redirect()->back()->with('success', 'Voucher berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('Error creating voucher', [
                'user_id' => auth()->id(),
                'input' => $request->except(['password']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal membuat voucher: ' . $e->getMessage()])->withInput();
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
        $transactions = $this->filterTransactions($request->transactions);
        $voucherDetails = $this->filterVoucherDetails($request->voucher_details);

        $validator = $this->validateVoucherRequest($request, $transactions, $voucherDetails, true);
        $this->validateStockAndHpp($request, $transactions, $validator);

        if ($validator->fails()) {
            Log::warning('Voucher update validation failed', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'input' => $request->except(['password']),
                'errors' => $validator->errors()->toArray(),
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $this->voucherService->updateVoucher($request, $id);
            return redirect()->back()->with('success', 'Voucher berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Voucher update failed', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'input' => $request->except(['password']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui voucher: ' . $e->getMessage()])->withInput();
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
            Log::error('Error fetching invoice details', [
                'invoice' => $request->invoice,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
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
            $data['stocks'] = Stock::select(['item', 'size', 'quantity'])->get();
            $data['transferStocks'] = TransferStock::select(['item', 'size', 'quantity'])->get();
            $data['usedStocks'] = UsedStock::select(['item', 'size', 'quantity'])->get();
            $data['pjStocks'] = collect($data['usedStocks'])
                ->map(function ($stock) {
                    return ['item' => $stock->item, 'size' => $stock->size, 'quantity' => $stock->quantity, 'source' => 'used_stocks'];
                })
                ->merge(
                    collect($data['transferStocks'])
                        ->map(function ($stock) {
                            return ['item' => $stock->item, 'size' => $stock->size, 'quantity' => $stock->quantity, 'source' => 'transfer_stocks'];
                        })
                );

            // Use transactionsData from prepareVoucherEditData, or fetch specific fields if needed
            // If you need different fields, ensure the query uses the voucher relationship
            if (empty($data['transactionsData'])) {
                $data['transactionsData'] = Transactions::whereHas('voucher', function ($query) {
                    $query->where('voucher_type', 'PB');
                })
                    ->select(['description', 'nominal', 'size'])
                    ->get()
                    ->map(function ($transaction) {
                        return [
                            'description' => $transaction->description,
                            'nominal' => $transaction->nominal,
                            'size' => $transaction->size,
                        ];
                    })->values()->toArray();
            }

            return view('voucher.voucher_edit', $data);
        } catch (\Exception $e) {
            Log::error('Voucher Edit Error', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman edit voucher']);
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
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus voucher']);
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
            Log::error('Voucher Detail Error', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
            Log::error('Company data not found for PDF', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Data perusahaan tidak ditemukan untuk pembuatan PDF']);
        } catch (\Exception $e) {
            Log::error('Error generating PDF', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal membuat PDF']);
        }
    }
}
