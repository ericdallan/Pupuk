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
use App\Models\Recipes;
use App\Models\Subsidiary;
use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\Transactions;
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
            $data['stocks'] = Stock::select(['item', 'size', 'quantity'])->get()->toArray();
            $data['transferStocks'] = TransferStock::select(['item', 'size', 'quantity'])->get()->toArray();
            $data['usedStocks'] = UsedStock::select(['item', 'size', 'quantity'])->get()->toArray();
            $data['pjStocks'] = collect($data['usedStocks'])
                ->map(function ($stock) {
                    return ['item' => $stock['item'], 'size' => $stock['size'], 'quantity' => $stock['quantity'], 'source' => 'used_stocks'];
                })
                ->merge(
                    collect($data['stocks'])
                        ->map(function ($stock) {
                            return ['item' => $stock['item'], 'size' => $stock['size'], 'quantity' => $stock['quantity'], 'source' => 'stocks'];
                        })
                )->toArray();
            $data['transactions'] = $data['transactionsData'];
            $data['recipes'] = Recipes::select(['id', 'product_name', 'size', 'nominal'])->get()->toArray();
            return view('voucher.voucher_page', $data);
        } catch (\Exception $e) {
            Log::error('Voucher Page Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman voucher: ' . $e->getMessage()]);
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
                if (!isset($transaction['description']) || empty($transaction['description'])) {
                    return false;
                }
                $isHpp = str_starts_with($transaction['description'], 'HPP ');
                $hasQuantity = isset($transaction['quantity']) && floatval($transaction['quantity']) >= 0.01;
                $hasNominal = isset($transaction['nominal']) && floatval($transaction['nominal']) >= 0;
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
            ->map(function ($detail) {
                return [
                    'account_code' => $detail['account_code'],
                    'account_name' => $detail['account_name'] ?? null,
                    'debit' => floatval($detail['debit'] ?? 0),
                    'credit' => floatval($detail['credit'] ?? 0),
                ];
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
            'voucher_type' => 'required|string|in:PJ,PG,PM,PB,LN,PH,PK,PYB,PYK,PYL,RPJ,RPB',
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
                function ($attribute, $value, $fail) use ($request, $isUpdate) {
                    if ($request->use_invoice === 'yes' && $request->use_existing_invoice !== 'yes' && !$isUpdate) {
                        if (Invoice::where('invoice', $value)->exists()) {
                            $fail('Nomor Invoice sudah digunakan. Silakan pilih nomor lain atau gunakan invoice yang sudah ada.');
                        }
                    }
                },
            ],
            'store' => [
                'nullable',
                'string',
                'max:255',
                'required_if:use_invoice,yes',
            ],
            'due_date' => 'required_if:use_invoice,yes|date',
            'total_debit' => 'required|numeric|min:0',
            'total_credit' => 'required|numeric|min:0',
            'transactions' => 'required_if:voucher_type,PB,PK,PH,PJ,PYB,PYK|array|min:1',
            'transactions.*.description' => 'required|string|max:255',
            'transactions.*.size' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->voucher_type, ['PB', 'PJ', 'PH', 'PK']) && empty($value)) {
                        $fail('Ukuran wajib diisi untuk tipe voucher PB, PJ, PH, atau PK.');
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
            'voucher_details.*.account_name' => 'required|string|max:255',
            'voucher_details.*.debit' => 'nullable|numeric|min:0',
            'voucher_details.*.credit' => 'nullable|numeric|min:0',
            'recipe_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->voucher_type === 'PK' && $request->use_stock === 'yes' && !$value) {
                        $fail('ID resep wajib diisi untuk voucher tipe PK ketika menggunakan stok.');
                    }
                    if ($value && !Recipes::where('id', $value)->exists()) {
                        $fail("Resep dengan ID {$value} tidak ditemukan.");
                    }
                },
            ],
            // 'use_stock' => [
            //     'nullable',
            //     'in:yes,no',
            //     function ($attribute, $value, $fail) use ($request) {
            //         if ($request->voucher_type === 'PK' && !in_array($value, ['yes', 'no'], true)) {
            //             $fail('Parameter use_stock harus bernilai "yes" atau "no" untuk voucher tipe PK.');
            //         }
            //         if ($request->voucher_type !== 'PK' && !empty($value)) {
            //             $fail('Parameter use_stock hanya berlaku untuk voucher tipe PK.');
            //         }
            //     },
            // ],
        ];

        if ($isUpdate) {
            $rules['voucher_number'] = 'required|string|max:255';
        }

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
                    if ($subsidiaryCodes->count() !== 1) {
                        $fail('Hanya satu kode subsidiary yang boleh digunakan ketika menggunakan invoice.');
                    }
                },
            ];
        }

        $messages = [
            'voucher_type.required' => 'Tipe voucher wajib diisi.',
            'voucher_details.required' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
            'voucher_details.min' => 'Rincian Voucher minimal harus memiliki satu baris dengan Kode Akun.',
            'voucher_details.*.account_code.required' => 'Kode Akun wajib diisi pada setiap baris Rincian Voucher.',
            'invoice.required_if' => 'Nomor Invoice wajib diisi jika menggunakan invoice.',
            'store.required_if' => 'Nama toko wajib diisi jika menggunakan invoice.',
            'due_date.required_if' => 'Tanggal jatuh tempo wajib diisi jika menggunakan invoice.',
            'transactions.required_if' => 'Rincian Transaksi diperlukan untuk voucher tipe PB, PK, PH, PJ, PYB, atau PYK.',
            'transactions.min' => 'Rincian Transaksi minimal harus memiliki satu baris.',
            'transactions.*.description.required' => 'Deskripsi wajib diisi untuk setiap transaksi.',
            'transactions.*.quantity.required' => 'Kuantitas wajib diisi dan harus lebih dari atau sama dengan 0.01.',
            'transactions.*.quantity.min' => 'Kuantitas minimal adalah 0.01.',
            'transactions.*.nominal.required' => 'Nominal wajib diisi untuk setiap transaksi.',
            // 'recipe_id.required_if' => 'ID resep wajib diisi untuk voucher tipe PK.',
            // 'use_stock.in' => 'Parameter use_stock harus bernilai "yes" atau "no".',
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
        if (!in_array($request->voucher_type, ['PH', 'PK', 'PJ', 'PYK'])) {
            return;
        }

        if ($request->voucher_type === 'PK' && $request->use_stock === 'yes') {
            $recipeId = $request->recipe_id;
            $recipe = Recipes::find($recipeId);
            if (!$recipe) {
                $validator->errors()->add('recipe_id', "Resep dengan ID {$recipeId} tidak ditemukan.");
                return;
            }
            $quantity = floatval($transactions[0]['quantity'] ?? 1);
            if ($quantity <= 0) {
                $validator->errors()->add('transactions.0.quantity', 'Kuantitas untuk voucher PK harus lebih dari 0.');
                return;
            }
            $recipeTransferStocks = \App\Models\RecipesTransfer::where('recipe_id', $recipeId)->get();
            foreach ($recipeTransferStocks as $index => $recipeTransferStock) {
                $transferStock = TransferStock::where('id', $recipeTransferStock->transfer_stock_id)
                    ->where('item', $recipeTransferStock->item)
                    ->where('size', $recipeTransferStock->size)
                    ->first();
                if (!$transferStock) {
                    $validator->errors()->add(
                        "recipe_transfer_stocks.{$index}",
                        "Stok untuk item {$recipeTransferStock->item} (Ukuran: {$recipeTransferStock->size}) tidak ditemukan di tabel transfer_stocks."
                    );
                } elseif ($transferStock->quantity < $recipeTransferStock->quantity * $quantity) {
                    $validator->errors()->add(
                        "recipe_transfer_stocks.{$index}",
                        "Stok untuk item {$recipeTransferStock->item} (Ukuran: {$recipeTransferStock->size}) tidak mencukupi. Tersedia: {$transferStock->quantity}, Dibutuhkan: " . ($recipeTransferStock->quantity * $quantity) . "."
                    );
                }
            }
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
                            "Stok untuk item {$item} (Ukuran: {$size}) tidak mencukupi di tabel used_stocks atau stocks. Tersedia: {$totalQuantity}, Dibutuhkan: {$quantity}."
                        );
                    }
                } elseif ($request->voucher_type === 'PYK') {
                    $found = false;
                    $modelPriorities = [UsedStock::class, TransferStock::class, Stock::class];
                    foreach ($modelPriorities as $model) {
                        $stock = $model::where('item', $item)->where('size', $size)->first();
                        if ($stock && $stock->quantity >= $quantity) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $validator->errors()->add(
                            $errorField,
                            "Stok untuk item {$item} (Ukuran: {$size}) tidak mencukupi di tabel manapun untuk PYK."
                        );
                    }
                }
            }
        }
    }

    /**
     * Calculate average HPP for an item based on PB transactions
     *
     * @param string $item
     * @param string|null $size
     * @return float
     */
    protected function calculateAverageHpp($item, $size = null)
    {
        $baseItem = trim(str_replace('HPP ', '', $item));
        $transactions = Transactions::join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', $baseItem)
            ->when($size, function ($query, $size) {
                return $query->where('transactions.size', $size);
            })
            ->pluck('transactions.nominal');

        return $transactions->isEmpty() ? 0 : round($transactions->average(), 2);
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
            $voucherNumber = $request->voucher_number === '[Auto Generate Number]'
                ? $this->voucherService->generateVoucherNumber($request->voucher_type)
                : $request->voucher_number;
            $this->voucherService->createVoucher($request, $voucherNumber);
            return redirect()->route('voucher_page')->with('success', 'Voucher berhasil dibuat.');
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
            return redirect()->route('voucher_page')->with('success', 'Voucher berhasil diperbarui.');
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
            $data['stocks'] = Stock::select(['item', 'size', 'quantity'])->get()->toArray();
            $data['transferStocks'] = TransferStock::select(['item', 'size', 'quantity'])->get()->toArray();
            $data['usedStocks'] = UsedStock::select(['item', 'size', 'quantity'])->get()->toArray();
            $data['pjStocks'] = collect($data['usedStocks'])
                ->map(function ($stock) {
                    return ['item' => $stock['item'], 'size' => $stock['size'], 'quantity' => $stock['quantity'], 'source' => 'used_stocks'];
                })
                ->merge(
                    collect($data['stocks'])
                        ->map(function ($stock) {
                            return ['item' => $stock['item'], 'size' => $stock['size'], 'quantity' => $stock['quantity'], 'source' => 'stocks'];
                        })
                )->toArray();
            return view('voucher.voucher_edit', $data);
        } catch (\Exception $e) {
            Log::error('Voucher Edit Error', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman edit voucher: ' . $e->getMessage()]);
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
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus voucher: ' . $e->getMessage()]);
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
            return redirect()->back()->withErrors(['error' => 'Gagal memuat detail voucher: ' . $e->getMessage()]);
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
            return redirect()->back()->withErrors(['error' => 'Gagal membuat PDF: ' . $e->getMessage()]);
        }
    }
}
