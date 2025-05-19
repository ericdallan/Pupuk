<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\voucherDetails;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Transactions;
use App\Models\InvoicePayment;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use App\Models\Subsidiary;

class VoucherController extends Controller
{
    public function voucher_page(Request $request)
    {
        $company = Company::select('company_name', 'director')->first();
        $query = Voucher::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('voucher_number', 'like', '%' . $request->search . '%')
                    ->orWhere('invoice', 'like', '%' . $request->search . '%');
            });
        }

        // Existing filters
        if ($request->has('voucher_type') && $request->voucher_type != '') {
            $query->where('voucher_type', $request->voucher_type);
        }

        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $query->whereYear('voucher_date', $request->year);
        }

        // Paginate results (10 per page)
        $vouchers = $query->with(['invoices', 'invoice_payments', 'transactions'])
            ->paginate(10)
            ->appends($request->query());

        // Fetch the earliest transaction for each stock item
        $openingStockTransactions = DB::table('transactions')
            ->select('t1.description as item', 't1.voucher_id', 't1.created_at')
            ->from('transactions as t1')
            ->join(DB::raw('(
            SELECT description, MIN(created_at) as min_created_at
            FROM transactions
            WHERE description NOT LIKE "HPP %"
            GROUP BY description
        ) as t2'), function ($join) {
                $join->on('t1.description', '=', 't2.description')
                    ->whereColumn('t1.created_at', 't2.min_created_at');
            })
            ->where('t1.description', 'NOT LIKE', 'HPP %')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('stocks')
                    ->whereColumn('stocks.item', 't1.description');
            })
            ->get()
            ->groupBy('voucher_id')
            ->mapWithKeys(function ($group, $voucher_id) {
                return [$voucher_id => $group->pluck('item')->toArray()];
            });

        // Map vouchers to include stock-related flags
        $vouchers->getCollection()->transform(function ($voucher) use ($openingStockTransactions) {
            $hasStock = $voucher->transactions()->whereHas('stock', function ($query) {
                $query->whereColumn('transactions.description', 'stocks.item');
            })->exists();
            $voucher->has_stock = $hasStock;

            $voucher->is_opening_stock = isset($openingStockTransactions[$voucher->id])
                ? $openingStockTransactions[$voucher->id]
                : [];

            return $voucher;
        });

        $transactionsData = $vouchers->filter(function ($voucher) {
            return $voucher->voucher_type === 'PB';
        })->flatMap(function ($voucher) {
            return $voucher->transactions->map(function ($transaction) {
                return [
                    'description' => $transaction->description,
                    'quantity' => $transaction->quantity,
                    'nominal' => $transaction->nominal,
                ];
            });
        })->values()->all();

        $accounts = ChartOfAccount::orderBy('account_type')
            ->orderBy('account_section')
            ->orderBy('account_subsection')
            ->orderBy('account_code')
            ->get()
            ->sortBy(function ($account) {
                $parts = explode('.', $account->account_code);
                if (count($parts) > 0) {
                    return intval(end($parts));
                }
                return null;
            })
            ->sortBy(function ($account) {
                $parts = explode('.', $account->account_code);
                return implode('.', array_slice($parts, 0, -1));
            });

        $sessionService = app('session');
        $sessionId = $sessionService->getId() ?? '';
        $sessionData = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        $userId = null;
        $admin = null;
        if ($sessionData && isset($sessionData->user_id)) {
            $userId = $sessionData->user_id;
            $admin = \App\Models\Admin::where('id', $userId)->first();
        }

        $storeNames = Subsidiary::pluck('store_name')->unique()->values();
        $existingInvoices = Voucher::select('vouchers.invoice')
            ->join('invoices', 'vouchers.invoice', '=', 'invoices.invoice')
            ->where('invoices.status', 'pending')
            ->whereNotNull('vouchers.invoice')
            ->distinct()
            ->pluck('vouchers.invoice')
            ->toArray();
        $subsidiaries = Subsidiary::all();
        $stocksData = Stock::all();

        $subsidiariesData = $subsidiaries->map(function ($subsidiary) {
            return [
                'subsidiary_code' => $subsidiary->subsidiary_code,
                'store_name' => $subsidiary->store_name,
                'account_name' => $subsidiary->account_name,
            ];
        })->values()->toArray();

        $accountsData = $accounts->map(function ($account) {
            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
            ];
        })->values()->toArray();

        return view('voucher.voucher_page', compact(
            'vouchers',
            'accounts',
            'company',
            'admin',
            'storeNames',
            'existingInvoices',
            'subsidiaries',
            'subsidiariesData',
            'accountsData',
            'stocksData',
            'transactionsData'
        ));
    }
    public function voucher_form(Request $request)
    {
        // Filter voucher_details to remove rows without account_code
        $voucherDetailsData = collect($request->voucher_details ?? [])
            ->filter(function ($detail) {
                return !empty($detail['account_code']);
            })
            ->values()
            ->toArray();

        $validator = Validator::make(
            array_merge($request->all(), ['voucher_details' => $voucherDetailsData]),
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
                    function ($attribute, $value, $fail) use ($request, $voucherDetailsData) {
                        $useInvoice = $request->use_invoice === 'yes';

                        // Check if any account_code in voucher_details is a subsidiary code
                        $hasSubsidiaryCode = collect($voucherDetailsData)->contains(function ($detail) {
                            return Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                        });

                        if ($useInvoice) {
                            if ($hasSubsidiaryCode) {
                                // If a subsidiary code is used, validate other codes against chart_of_accounts
                                if (
                                    !ChartOfAccount::where('account_code', $value)->exists() &&
                                    !Subsidiary::where('subsidiary_code', $value)->exists()
                                ) {
                                    $fail("Kode akun {$value} tidak valid di tabel chart_of_accounts atau subsidiaries.");
                                }
                            } else {
                                // If no subsidiary code is used, validate against subsidiaries
                                if (!Subsidiary::where('subsidiary_code', $value)->exists()) {
                                    $fail("Kode akun {$value} tidak valid di tabel subsidiaries.");
                                }
                            }
                        } else {
                            // If use_invoice is no, validate against chart_of_accounts
                            if (!ChartOfAccount::where('account_code', $value)->exists()) {
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

        DB::beginTransaction();
        try {
            // Generate voucher number
            $voucherNumber = $this->generateVoucherNumber($request->voucher_type);

            // Prepare the data for Voucher::create()
            $voucherData = [
                'voucher_number' => $voucherNumber,
                'voucher_type' => $request->voucher_type,
                'voucher_date' => $request->voucher_date,
                'voucher_day' => $request->voucher_day,
                'prepared_by' => $request->prepared_by,
                'approved_by' => $request->approved_by,
                'given_to' => $request->given_to,
                'transaction' => $request->transaction,
                'store' => $request->store,
                'total_debit' => $request->total_debit,
                'total_credit' => $request->total_credit,
                'invoice' => $request->use_invoice === 'yes' ? $request->invoice : null,
            ];

            // Save to vouchers table
            $voucher = Voucher::create($voucherData);

            // Save Transaction Details
            if ($request->has('transactions') && is_array($request->transactions)) {
                foreach ($request->transactions as $transaction) {
                    Transactions::create([
                        'voucher_id' => $voucher->id,
                        'description' => !empty($transaction['description']) ? $transaction['description'] : null,
                        'quantity' => $transaction['quantity'] ?? 1,
                        'nominal' => $transaction['nominal'] ?? 0.00,
                    ]);
                }
            }

            // Save Stock Updates
            if ($request->has('transactions') && is_array($request->transactions)) {
                foreach ($request->transactions as $transaction) {
                    if (!empty($transaction['description']) && isset($transaction['quantity'])) {
                        $item = $transaction['description'];
                        $quantity = $transaction['quantity'] ?? 1;

                        // Only process stock updates for PJ (sale) or PB (purchase) voucher types
                        if ($request->voucher_type === 'PJ' || $request->voucher_type === 'PB') {
                            $isSale = $request->voucher_type === 'PJ';
                            $isPurchase = $request->voucher_type === 'PB';

                            // Find existing stock record
                            $stock = Stock::where('item', $item)->first();

                            if (!$stock) {
                                // Create new stock record if item doesn't exist
                                Stock::create([
                                    'item' => $item,
                                    'quantity' => $isPurchase ? $quantity : ($isSale ? -$quantity : 0),
                                ]);
                            } else {
                                // Update existing stock record
                                if ($isPurchase) {
                                    $stock->quantity += $quantity;
                                } elseif ($isSale) {
                                    $stock->quantity -= $quantity;
                                }
                                $stock->save();

                                // Check if quantity goes negative, but allow for items starting with "HPP ..."
                                if ($stock->quantity < 0 && !str_starts_with($item, 'HPP ')) {
                                    throw new \Exception("Stock untuk item {$item} tidak mencukupi.");
                                }
                            }
                        }
                    }
                }
            }

            // Save Voucher Details
            foreach ($voucherDetailsData as $detail) {
                VoucherDetails::create([
                    'voucher_id' => $voucher->id,
                    'account_code' => $detail['account_code'],
                    'account_name' => $detail['account_name'] ?? null,
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                ]);
            }

            // Handle Invoice creation or update
            if ($request->use_invoice === 'yes') {
                // Find the subsidiary_code from voucher details
                $subsidiaryCode = collect($voucherDetailsData)->firstWhere(function ($detail) {
                    return Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                })['account_code'] ?? null;

                if (!$subsidiaryCode) {
                    throw new \Exception('No valid subsidiary_code found in voucher details.');
                }

                // Fetch the total_amount from voucher_details for the given voucher_id and subsidiary_code
                $voucherDetails = DB::table('voucher_details')
                    ->where('voucher_id', $voucher->id)
                    ->where('account_code', $subsidiaryCode)
                    ->selectRaw('SUM(debit) - SUM(credit) as total_amount')
                    ->first();

                if (!$voucherDetails || is_null($voucherDetails->total_amount)) {
                    throw new \Exception("No voucher details found for subsidiary_code {$subsidiaryCode} and voucher_id {$voucher->id}, or total_amount is null.");
                }

                $totalAmount = abs($voucherDetails->total_amount); // Use absolute value to ensure positive total_amount

                if ($totalAmount <= 0) {
                    throw new \Exception("Total amount for subsidiary_code {$subsidiaryCode} and voucher_id {$voucher->id} is invalid or zero.");
                }

                $invoice = Invoice::where('invoice', $request->invoice)->first();

                if ($request->use_existing_invoice === 'yes' && !$invoice) {
                    throw new \Exception('Selected existing invoice not found.');
                }

                if (!$invoice) {
                    // Create new invoice (initial sales voucher)
                    Invoice::create([
                        'invoice' => $request->invoice,
                        'voucher_number' => $voucher->voucher_number,
                        'subsidiary_code' => $subsidiaryCode,
                        'status' => 'pending',
                        'due_date' => $request->dueDate,
                        'total_amount' => $totalAmount,
                        'remaining_amount' => $totalAmount,
                    ]);
                } else {
                    // Update existing invoice (payment voucher)
                    // For payment, use the total_amount as the payment amount to deduct
                    $invoice->remaining_amount -= $totalAmount;
                    $invoice->status = $invoice->remaining_amount <= 0 ? 'paid' : 'pending';
                    $invoice->save();

                    // Link payment voucher to invoice
                    $invoice->invoice_payments()->create([
                        'voucher_id' => $voucher->id,
                        'amount' => $totalAmount,
                        'payment_date' => $request->voucher_date,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Voucher berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating voucher or invoice: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat membuat voucher atau invoice: ' . $e->getMessage()])->withInput();
        }
    }
    public function get_invoice_details(Request $request)
    {
        $invoice = Invoice::where('invoice', $request->invoice)->first();
        if ($invoice) {
            return response()->json([
                'total_amount' => $invoice->total_amount,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
            ]);
        }
        return response()->json(['error' => 'Invoice not found'], 404);
    }
    public function voucher_edit($id)
    {
        // Fetch company with required fields
        $company = Company::select('company_name', 'director')->first();
        if (!$company) {
            return redirect()->back()->withErrors(['message' => 'No company found.']);
        }

        // Fetch voucher with relations
        $voucher = Voucher::with(['voucherDetails', 'transactions'])->findOrFail($id);

        // Fetch chart of accounts
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        if ($accounts->isEmpty()) {
            return redirect()->back()->withErrors(['message' => 'No chart of accounts found.']);
        }
        $accountsData = $accounts->map(function ($account) {
            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
            ];
        })->values()->toArray();

        // Fetch existing invoices
        $existingInvoices = Invoice::pluck('invoice')->unique()->values()->toArray();

        // Fetch store names
        $storeNames = Subsidiary::pluck('store_name')->unique()->values()->toArray();
        if (empty($storeNames)) {
            $storeNames = [];
        }

        // Fetch subsidiaries
        $subsidiariesData = Subsidiary::select('subsidiary_code', 'account_name')
            ->orderBy('subsidiary_code')
            ->get()
            ->map(function ($subsidiary) {
                return [
                    'subsidiary_code' => $subsidiary->subsidiary_code,
                    'account_name' => $subsidiary->account_name,
                ];
            })->toArray();
        if (empty($subsidiariesData)) {
            $subsidiariesData = [];
        }

        // Fetch stock data
        $stocks = Stock::select('item')
            ->orderBy('item')
            ->get()
            ->map(function ($stock) {
                return [
                    'item' => $stock->item,
                ];
            })->values()->toArray();
        if (empty($stocks)) {
            $stocks = [];
        }

        // Fetch transaction data for HPP calculation (for PB vouchers)
        $transactionsData = Transactions::whereHas('voucher', function ($query) {
            $query->where('voucher_type', 'PB');
        })
            ->where('description', 'NOT LIKE', 'HPP %')
            ->get()
            ->map(function ($transaction) {
                return [
                    'description' => $transaction->description,
                    'quantity' => $transaction->quantity,
                    'nominal' => $transaction->nominal,
                ];
            })->values()->toArray();

        // Get heading text for voucher type
        $headingText = $this->getVoucherHeading($voucher->voucher_type);

        $dueDate = '';
        if ($voucher->invoice) {
            $invoice = \App\Models\Invoice::where('invoice', $voucher->invoice)->first();
            if ($invoice) {
                // Check if due_date is a Carbon instance or parse it if it's a string
                if ($invoice->due_date instanceof \Carbon\Carbon) {
                    $dueDate = $invoice->due_date->format('Y-m-d');
                } elseif (is_string($invoice->due_date) && !empty($invoice->due_date)) {
                    $dueDate = \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d');
                }
            }
        }

        return view('voucher.voucher_edit', compact(
            'voucher',
            'headingText',
            'accounts',
            'company',
            'existingInvoices',
            'storeNames',
            'subsidiariesData',
            'accountsData',
            'stocks',
            'transactionsData',
            'dueDate'
        ));
    }
    public function voucher_update(Request $request, $id)
    {
        // Filter voucher_details to remove rows without account_code
        $voucherDetailsData = collect($request->voucher_details ?? [])
            ->filter(function ($detail) {
                return !empty($detail['account_code']);
            })
            ->values()
            ->toArray();

        // Filter transactions to remove invalid entries (empty description or zero values, unless HPP)
        /** @var array $transactionsData */
        $transactionsData = collect($request->transactions ?? [])
            ->filter(function ($transaction) {
                $hasDescription = !empty($transaction['description']);
                $isHpp = str_starts_with($transaction['description'], 'HPP ');
                $hasQuantity = isset($transaction['quantity']) && floatval($transaction['quantity']) > 0;
                $hasNominal = isset($transaction['nominal']) && floatval($transaction['nominal']) >= 0;
                return $hasDescription && ($isHpp || ($hasQuantity && $hasNominal));
            })
            ->values()
            ->toArray();

        // Validate request
        $validator = Validator::make(
            array_merge($request->all(), [
                'voucher_details' => $voucherDetailsData,
                'transactions' => $transactionsData,
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
                            if (Invoice::where('invoice', $value)->exists()) {
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
                    function ($attribute, $value, $fail) use ($request, $voucherDetailsData) {
                        $useInvoice = $request->use_invoice === 'yes';
                        $hasSubsidiaryCode = collect($voucherDetailsData)->contains(function ($detail) {
                            return Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                        });

                        if ($useInvoice && !$hasSubsidiaryCode) {
                            if (!Subsidiary::where('subsidiary_code', $value)->exists()) {
                                $fail("Kode akun {$value} tidak valid di tabel subsidiaries.");
                            }
                        } else {
                            if (
                                !ChartOfAccount::where('account_code', $value)->exists() &&
                                !Subsidiary::where('subsidiary_code', $value)->exists()
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
            DB::beginTransaction();

            $voucher = Voucher::with(['transactions', 'voucherDetails'])->findOrFail($id);

            // Calculate totals
            $totalNominal = 0;
            $transactionItems = [];
            foreach ($transactionsData as $index => $transaction) {
                $quantity = floatval($transaction['quantity']);
                $nominal = floatval($transaction['nominal']);
                $totalNominal += $quantity * $nominal;
                $transactionItems[$index] = [
                    'description' => $transaction['description'],
                    'quantity' => $quantity,
                    'nominal' => $nominal,
                    'is_hpp' => str_starts_with($transaction['description'], 'HPP '),
                ];
            }

            $totalDebit = 0;
            $totalCredit = 0;
            $subsidiaryCount = 0;
            foreach ($voucherDetailsData as $detail) {
                $debit = floatval($detail['debit'] ?? 0);
                $credit = floatval($detail['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    throw new \Exception("Debit dan kredit tidak boleh keduanya non-nol untuk kode akun: {$detail['account_code']}");
                }

                $totalDebit += $debit;
                $totalCredit += $credit;

                if ($request->use_invoice === 'yes') {
                    $isSubsidiary = Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                    if ($isSubsidiary) {
                        $subsidiaryCount++;
                    }
                    if ($subsidiaryCount > 1) {
                        throw new \Exception("Hanya satu kode subsidiary yang dapat digunakan saat invoice diaktifkan.");
                    }
                }
            }

            // Validate totals
            if (round($totalNominal, 2) !== round($totalDebit, 2) || round($totalNominal, 2) !== round($totalCredit, 2)) {
                throw new \Exception('Total nominal harus sama dengan total debit dan total kredit.');
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \Exception('Total debit harus sama dengan total kredit.');
            }

            // Validate HPP transactions for PJ vouchers
            if ($request->voucher_type === 'PJ') {
                $stockItems = collect($transactionItems)
                    ->filter(fn ($item) => !$item['is_hpp'])
                    ->pluck('description')
                    ->toArray();
                foreach ($transactionItems as $index => $item) {
                    if ($item['is_hpp']) {
                        $stockItem = str_replace('HPP ', '', $item['description']);
                        if (!in_array($stockItem, $stockItems)) {
                            throw new \Exception("Transaksi HPP untuk item {$stockItem} tidak memiliki transaksi stok yang sesuai.");
                        }
                        $stockIndex = array_search($stockItem, array_column($transactionItems, 'description'));
                        if ($stockIndex !== false && $transactionItems[$stockIndex]['quantity'] != $item['quantity']) {
                            throw new \Exception("Kuantitas HPP untuk item {$stockItem} tidak sesuai dengan kuantitas stok.");
                        }
                    }
                }

                // Validate at least one non-HPP transaction
                $nonHppCount = collect($transactionItems)->filter(fn ($item) => !$item['is_hpp'])->count();
                if ($nonHppCount === 0) {
                    throw new \Exception('Voucher Penjualan harus memiliki setidaknya satu transaksi non-HPP.');
                }
            }

            // Validate stock availability for PJ vouchers
            if ($request->voucher_type === 'PJ') {
                foreach ($transactionsData as $transaction) {
                    if (!str_starts_with($transaction['description'], 'HPP ')) {
                        $item = $transaction['description'];
                        $quantity = floatval($transaction['quantity']);
                        $stock = Stock::where('item', $item)->first();
                        if ($stock && $stock->quantity < $quantity) {
                            throw new \Exception("Stock untuk item {$item} tidak mencukupi. Tersedia: {$stock->quantity}, Dibutuhkan: {$quantity}.");
                        }
                    }
                }
            }

            // Revert previous stock changes
            if (in_array($voucher->voucher_type, ['PJ', 'PB'])) {
                foreach ($voucher->transactions as $transaction) {
                    if (!str_starts_with($transaction->description, 'HPP ')) {
                        $item = $transaction->description;
                        $quantity = floatval($transaction->quantity);
                        $stock = Stock::where('item', $item)->first();

                        if ($stock) {
                            if ($voucher->voucher_type === 'PJ') {
                                $stock->quantity += $quantity; // Revert sale
                            } elseif ($voucher->voucher_type === 'PB') {
                                $stock->quantity -= $quantity; // Revert purchase
                            }
                            $stock->save();

                            if ($stock->quantity < 0) {
                                throw new \Exception("Stock untuk item {$item} tidak mencukupi setelah pembalikan.");
                            }
                        }
                    }
                }
            }

            // Apply new stock updates
            if (in_array($request->voucher_type, ['PJ', 'PB'])) {
                foreach ($transactionsData as $transaction) {
                    if (!str_starts_with($transaction['description'], 'HPP ')) {
                        $item = $transaction['description'];
                        $quantity = floatval($transaction['quantity']);

                        $isSale = $request->voucher_type === 'PJ';
                        $isPurchase = $request->voucher_type === 'PB';

                        $stock = Stock::where('item', $item)->first();

                        if (!$stock) {
                            $stock = Stock::create([
                                'item' => $item,
                                'quantity' => $isPurchase ? $quantity : ($isSale ? -$quantity : 0),
                            ]);
                        } else {
                            if ($isPurchase) {
                                $stock->quantity += $quantity;
                            } elseif ($isSale) {
                                $stock->quantity -= $quantity;
                            }
                            $stock->save();

                            if ($stock->quantity < 0) {
                                throw new \Exception("Stock untuk item {$item} tidak mencukupi.");
                            }
                        }
                    }
                }
            }

            // Handle invoice updates
            $invoice = null;
            if ($request->use_invoice === 'yes') {
                if ($request->use_existing_invoice === 'yes') {
                    $invoice = Invoice::where('invoice', $request->invoice)->first();
                    if (!$invoice) {
                        throw new \Exception("Invoice dengan nomor {$request->invoice} tidak ditemukan.");
                    }
                } else {
                    $invoice = Invoice::create([
                        'invoice' => $request->invoice,
                        'due_date' => Carbon::parse($request->due_date),
                        'amount' => $totalDebit, // Assume total_debit is the invoice amount
                        'remaining_amount' => $totalDebit, // Initialize remaining_amount
                        'store' => $request->store,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Create/update invoice payment for all voucher types when invoice is used
                // Calculate payment amount (using total_debit, adjustable per voucher type if needed)
                $paymentAmount = round($totalDebit, 2);
                if ($invoice->total_amount < $paymentAmount) {
                    throw new \Exception("Jumlah pembayaran ({$paymentAmount}) melebihi tagihan invoice ({$invoice->total_amount}).");
                }

                $payment = InvoicePayment::where('voucher_id', $voucher->id)->first();
                if ($payment) {
                    // Adjust remaining_amount for the old payment amount
                    $oldPaymentAmount = $payment->amount;
                    $invoice->remaining_amount += $oldPaymentAmount; // Revert the old payment
                    // Update existing payment
                    $payment->update([
                        'invoice_id' => $invoice->id,
                        'amount' => $paymentAmount, // Ensure amount is updated
                        'payment_date' => Carbon::parse($request->voucher_date),
                        'updated_at' => now(),
                    ]);
                    // Deduct the new payment amount
                    $invoice->remaining_amount -= $paymentAmount;
                } else {
                    // Create new payment
                    InvoicePayment::create([
                        'voucher_id' => $voucher->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $paymentAmount,
                        'payment_date' => Carbon::parse($request->voucher_date),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    // Deduct the payment amount
                    $invoice->remaining_amount -= $paymentAmount;
                }

                // Save the updated remaining_amount
                $invoice->updated_at = now();
                $invoice->save();

                // Validate remaining_amount is not negative (should be caught by the earlier check, but adding for safety)
                if ($invoice->remaining_amount < 0) {
                    throw new \Exception("Sisa tagihan invoice menjadi negatif ({$invoice->remaining_amount}).");
                }
            } else {
                // Remove any existing invoice payment if use_invoice is 'no'
                $payment = InvoicePayment::where('voucher_id', $voucher->id)->first();
                if ($payment) {
                    // Revert the payment amount to remaining_amount
                    $invoice = Invoice::find($payment->invoice_id);
                    if ($invoice) {
                        $invoice->remaining_amount += $payment->amount;
                        $invoice->updated_at = now();
                        $invoice->save();
                    }
                    $payment->delete();
                }
            }

            // Update voucher
            $voucher->update([
                'voucher_number' => $request->voucher_number,
                'voucher_type' => $request->voucher_type,
                'voucher_date' => Carbon::parse($request->voucher_date),
                'voucher_day' => $request->voucher_day,
                'prepared_by' => $request->prepared_by,
                'given_to' => $request->given_to,
                'approved_by' => $request->approved_by,
                'transaction' => $request->transaction,
                'use_invoice' => $request->use_invoice,
                'use_existing_invoice' => $request->use_existing_invoice,
                'invoice' => $request->use_invoice === 'yes' ? $request->invoice : null,
                'store' => $request->use_invoice === 'yes' ? $request->store : null,
                'total_nominal' => $totalNominal,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'updated_at' => now(),
            ]);

            // Sync transactions
            $voucher->transactions()->delete();
            foreach ($transactionsData as $transaction) {
                $voucher->transactions()->create([
                    'description' => $transaction['description'],
                    'quantity' => floatval($transaction['quantity']),
                    'nominal' => floatval($transaction['nominal']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Sync voucher details
            $voucher->voucherDetails()->delete();
            foreach ($voucherDetailsData as $detail) {
                $voucher->voucherDetails()->create([
                    'account_code' => $detail['account_code'],
                    'account_name' => $detail['account_name'],
                    'debit' => floatval($detail['debit'] ?? 0),
                    'credit' => floatval($detail['credit'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Voucher berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Voucher update failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('message', 'Gagal memperbarui voucher: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function voucher_delete($id)
    {
        try {
            DB::beginTransaction();

            // 1. Find the voucher to be deleted
            $voucherToDelete = Voucher::findOrFail($id);

            // 2. Check invoice and payment conditions to determine if deletion is allowed
            if ($voucherToDelete->has_stock) {
                throw new \Exception('Voucher tidak dapat dihapus karena memiliki data stok.');
            }

            $hasInvoices = $voucherToDelete->invoices()->exists();
            $hasInvoiceWithPayments = $voucherToDelete->invoices()->whereHas('invoice_payments')->exists();

            if ($hasInvoices && $hasInvoiceWithPayments) {
                throw new \Exception('Voucher tidak dapat dihapus karena memiliki invoice yang terkait dengan pembayaran.');
            }

            // 3. Delete related voucher details
            $voucherToDelete->voucherDetails()->delete();

            // 4. Get related transactions before deleting them
            $transactions = $voucherToDelete->transactions()->get();

            // 5. Update stock quantities and delete stock if quantity = 0 and no related transactions
            $transactionQuantities = $transactions->groupBy('description')->map(function ($group) {
                return $group->sum('quantity');
            });

            foreach ($transactionQuantities as $description => $totalQuantity) {
                $stock = Stock::where('item', $description)->first();

                if ($stock && $totalQuantity > 0) {
                    if ($voucherToDelete->voucher_type === 'PJ') {
                        $stock->quantity += $totalQuantity;
                    } elseif ($voucherToDelete->voucher_type === 'PB') {
                        $stock->quantity -= $totalQuantity;
                        if ($stock->quantity < 0) {
                            $stock->quantity = 0;
                        }
                    }
                    $stock->save();

                    if ($stock->quantity == 0) {
                        $remainingTransactions = DB::table('transactions')
                            ->where('description', $description)
                            ->where('voucher_id', '!=', $voucherToDelete->id)
                            ->count();
                        if ($remainingTransactions == 0) {
                            $stock->delete();
                        }
                    }
                }
            }

            // 6. Delete related transactions
            $voucherToDelete->transactions()->delete();

            // 7. Handle invoice payments deletion if they exist
            if ($hasInvoiceWithPayments) {
                // Get all invoice payments for this voucher
                $invoicePayments = $voucherToDelete->invoice_payments()->get();

                // Update remaining_amount in related invoices
                foreach ($invoicePayments as $payment) {
                    $invoice = Invoice::find($payment->invoice_id);
                    if ($invoice) {
                        // Adjust remaining_amount: add back the payment amount
                        $invoice->remaining_amount += $payment->amount;
                        $invoice->save();
                    }
                }
                // Delete invoice payments related to the voucher
                $voucherToDelete->invoice_payments()->delete();

                // Check if the related invoice has no remaining payments and delete it if so
                $invoiceIds = InvoicePayment::where('voucher_id', $voucherToDelete->id)->pluck('invoice_id')->unique();
                foreach ($invoiceIds as $invoiceId) {
                    $remainingPayments = InvoicePayment::where('invoice_id', $invoiceId)->count();
                    if ($remainingPayments == 0) {
                        Invoice::where('id', $invoiceId)->delete();
                    }
                }
            }

            // 8. Delete the voucher itself
            $voucherToDelete->delete();

            DB::commit();
            return redirect()->route('voucher_page')->with('success', 'Voucher dan semua data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Error deleting voucher', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->withErrors(['message' => 'Gagal menghapus voucher: ' . $e->getMessage()]);
        }
    }
    private function generateVoucherNumber($voucherType)
    {
        $lastVoucher = Voucher::where('voucher_type', $voucherType)
            ->orderBy('voucher_number', 'desc')
            ->first();

        if ($lastVoucher) {
            $lastNumber = intval(substr($lastVoucher->voucher_number, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $voucherType . '-' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    public function voucher_detail($id)
    {
        $company = Company::select('company_name', 'director')->firstOrFail();
        $voucher = Voucher::findOrFail($id);
        $voucherDetails = VoucherDetails::where('voucher_id', $id)->get();
        $voucherTransactions = Transactions::where('voucher_id', $id)->get();

        // Determine the heading text based on voucher type
        $headingText = $this->getVoucherHeading($voucher->voucher_type);

        return view('voucher.voucher_detail', compact('voucher', 'headingText', 'voucherDetails', 'voucherTransactions', 'company'));
    }
    private function getVoucherHeading($voucherType)
    {
        switch ($voucherType) {
            case 'PJ':
                return 'Penjualan';
            case 'PG':
                return 'Pengeluaran';
            case 'PM':
                return 'Pemasukan';
            case 'PB':
                return 'Pembelian';
            case 'LN':
                return 'Lainnya';
            default:
                return 'Voucher';
        }
    }
    public function generatePdf($id)
    {
        $voucher = Voucher::findOrFail($id);
        $details = voucherDetails::where('voucher_id', $id)->get();
        $transaction = Transactions::where('voucher_id', $id)->get();
        $companyLogo = \App\Models\Company::value('logo');

        try {
            $company = Company::select('company_name', 'phone', 'director', 'email', 'address')->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Company data not found when trying to generate PDF for voucher ID: {$id}");
            return redirect()->back()->with('error', 'Tidak dapat membuat PDF karena data perusahaan tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error("An unexpected error occurred while fetching company data for PDF generation of voucher ID: {$id}. Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pembuatan PDF.');
        }

        $headingText = $this->getVoucherHeading($voucher->voucher_type);
        $pdf = Pdf::loadView('voucher.voucher_pdf', compact('voucher', 'details', 'headingText', 'company', 'transaction', 'companyLogo'));
        // Optional: Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('voucher-' . $voucher->voucher_number . '.pdf');
    }
}
