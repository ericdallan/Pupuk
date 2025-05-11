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

        if ($request->has('voucher_type') && $request->voucher_type != '') {
            $query->where('voucher_type', $request->voucher_type);
        }

        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $query->whereYear('voucher_date', $request->year);
        }

        // Eager-load invoices and invoice_payments
        $voucher = $query->with(['invoices', 'invoice_payments'])->get();

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
        $userId = $sessionService->get('user_id');
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
            'voucher',
            'accounts',
            'company',
            'admin',
            'storeNames',
            'existingInvoices',
            'subsidiaries',
            'subsidiariesData',
            'accountsData'
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

                                // Check if quantity goes negative
                                if ($stock->quantity < 0) {
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
                $subsidiaryCode = collect($voucherDetailsData)->firstWhere(function ($detail) {
                    return Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                })['account_code'] ?? null;

                if (!$subsidiaryCode) {
                    throw new \Exception('No valid subsidiary_code found in voucher details.');
                }

                $invoice = Invoice::where('invoice', $request->invoice)->first();

                if ($request->use_existing_invoice === 'yes' && !$invoice) {
                    throw new \Exception('Selected existing invoice not found.');
                }

                $totalAmount = $request->total_debit; // Assuming total_debit represents payment amount

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
                    $invoice->remaining_amount -= $totalAmount;
                    $invoice->status = $invoice->remaining_amount <= 0 ? 'paid' : 'pending';
                    $invoice->save();

                    // Link payment voucher to invoice
                    $invoice->payment_vouchers()->create([
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
            // Handle case where no company is found
            return redirect()->back()->withErrors(['message' => 'No company found.']);
        }

        // Fetch voucher with relations
        $voucher = Voucher::with(['voucherDetails', 'transactions'])->findOrFail($id);

        // Fetch chart of accounts
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        // Fetch existing invoices (adjust query based on your Invoice model)
        $existingInvoices = Invoice::pluck('invoice')->unique()->values()->toArray();

        // Fetch store names (adjust query based on your Store model)
        $storeNames = Subsidiary::pluck('store_name')->unique()->values()->toArray();

        // Fetch subsidiaries (adjust query based on your Subsidiary model)
        $subsidiariesData = Subsidiary::select('subsidiary_code', 'account_name')
            ->orderBy('subsidiary_code')
            ->get()
            ->map(function ($subsidiary) {
                return [
                    'subsidiary_code' => $subsidiary->subsidiary_code,
                    'account_name' => $subsidiary->account_name,
                ];
            })->toArray();

        // Get heading text for voucher type
        $headingText = $this->getVoucherHeading($voucher->voucher_type);

        // Get user_id from the sessions table
        $sessionService = app('session');
        $sessionId = $sessionService->getId() ?? '';
        // Get user_id from the sessions table
        $sessionService = app('session');
        $sessionId = $sessionService->getId() ?? '';
        $userId = $sessionService->get('user_id');
        $sessionData = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        $userId = null;
        $admin = null;
        if ($sessionData && isset($sessionData->user_id)) {
            $userId = $sessionData->user_id;
            // Fetch the admin based on the user_id
            $admin = \App\Models\Admin::where('id', $userId)->first();
        }
        $accountsData = $accounts->map(function ($account) {
            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
            ];
        })->values()->toArray();

        return view('voucher.voucher_edit', compact(
            'voucher',
            'headingText',
            'accounts',
            'company',
            'existingInvoices',
            'storeNames',
            'subsidiariesData',
            'admin',
            'accountsData',
        ));
    }
    public function voucher_update(Request $request, $id)
    {
        // Validate request
        $validated = $request->validate([
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
            'invoice' => 'nullable|string|max:255',
            'store' => 'nullable|string|max:255',
            'transactions' => 'required|array|min:1',
            'transactions.*.description' => 'nullable|string|max:255',
            'transactions.*.quantity' => 'nullable|numeric|min:1',
            'transactions.*.nominal' => 'nullable|numeric|min:0',
            'voucher_details' => 'required|array|min:1',
            'voucher_details.*.account_code' => 'required|string|max:50',
            'voucher_details.*.account_name' => 'required|string|max:255',
            'voucher_details.*.debit' => 'nullable|numeric|min:0',
            'voucher_details.*.credit' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $voucher = Voucher::findOrFail($id);

            // Calculate totals
            $totalNominal = 0;
            foreach ($request->transactions as $transaction) {
                $quantity = floatval($transaction['quantity'] ?? 1);
                $nominal = floatval($transaction['nominal'] ?? 0);
                $totalNominal += $quantity * $nominal;
            }

            $totalDebit = 0;
            $totalCredit = 0;
            $subsidiaryCount = 0;
            foreach ($request->voucher_details as $detail) {
                $debit = floatval($detail['debit'] ?? 0);
                $credit = floatval($detail['credit'] ?? 0);

                // Ensure debit and credit are mutually exclusive
                if ($debit > 0 && $credit > 0) {
                    throw new \Exception("Debit and credit cannot both be non-zero for account code: {$detail['account_code']}");
                }

                $totalDebit += $debit;
                $totalCredit += $credit;

                // Validate account codes
                $accountCode = $detail['account_code'];
                if ($request->use_invoice === 'yes') {
                    $isSubsidiary = Subsidiary::where('subsidiary_code', $accountCode)->exists();
                    $isAccount = ChartOfAccount::where('account_code', $accountCode)->exists();
                    if ($isSubsidiary) {
                        $subsidiaryCount++;
                    }
                    if (!$isSubsidiary && !$isAccount) {
                        throw new \Exception("Invalid account code: {$accountCode}");
                    }
                    // Ensure only one subsidiary code is used
                    if ($subsidiaryCount > 1) {
                        throw new \Exception("Only one subsidiary code can be used when invoice is enabled.");
                    }
                } else {
                    if (!ChartOfAccount::where('account_code', $accountCode)->exists()) {
                        throw new \Exception("Invalid account code: {$accountCode}");
                    }
                }
            }

            // Validate totals
            if (round($totalNominal, 2) !== round($totalDebit, 2) || round($totalNominal, 2) !== round($totalCredit, 2)) {
                throw new \Exception('Total nominal must equal total debit and total credit.');
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \Exception('Total debit must equal total credit.');
            }

            if ($request->use_invoice === 'yes' && $request->use_existing_invoice === 'yes' && $request->invoice) {
                $invoice = Invoice::where('invoice', $request->invoice)->first();

                if (!$invoice) {
                    throw new \Exception("Nomor invoice tidak valid: {$request->invoice}");
                }

                // Cari pembayaran voucher yang spesifik untuk invoice ini
                $paymentOnInvoice = $invoice->payment_vouchers()->where('voucher_id', $voucher->id)->first();

                // Cari pembayaran voucher secara umum (mungkin terkait invoice lain sebelumnya)
                $generalPayment = InvoicePayment::where('voucher_id', $voucher->id)->first();

                if ($paymentOnInvoice) {
                    // Kasus 1: Voucher sudah memiliki pembayaran terkait invoice ini, update
                    $previousAmount = $paymentOnInvoice->amount;
                    $paymentOnInvoice->update([
                        'amount' => $totalNominal,
                        'payment_date' => Carbon::parse($request->voucher_date),
                    ]);

                    // Update informasi invoice berdasarkan total pembayaran terkait
                    $totalPaid = $invoice->payment_vouchers()->sum('amount');
                    $remainingBalance = $invoice->total_amount - $totalPaid;

                    $invoice->update([
                        'remaining_amount' => max(0, $remainingBalance),
                        'status' => $remainingBalance <= 0 ? 'paid' : 'pending',
                    ]);
                } elseif ($invoice && !$paymentOnInvoice && $generalPayment) {
                    // Kasus 2: Voucher ada di invoices, tidak ada pembayaran spesifik untuk invoice ini,
                    //         tetapi ada pembayaran umum di invoice_payments.
                    //         Asumsikan kita ingin memindahkan/mengaitkan pembayaran ini ke invoice yang dipilih.
                    $previousAmount = $generalPayment->amount;
                    $generalPayment->update([
                        'invoice_id' => $invoice->id,
                        'amount' => $totalNominal,
                        'payment_date' => Carbon::parse($request->voucher_date),
                        'updated_at' => now(),
                    ]);

                    // Update informasi invoice berdasarkan total pembayaran terkait
                    $totalPaid = $invoice->payment_vouchers()->sum('amount');
                    $remainingBalance = $invoice->total_amount - $totalPaid;

                    $invoice->update([
                        'remaining_amount' => max(0, $remainingBalance),
                        'status' => $remainingBalance <= 0 ? 'paid' : 'pending',
                    ]);
                } elseif ($invoice && !$paymentOnInvoice && !$generalPayment) {
                    // Kasus 3: Voucher ada di invoices, tidak ada pembayaran terkait sama sekali.
                    //         Buat entri baru di invoice_payments.
                    $invoice->payment_vouchers()->create([
                        'voucher_id' => $voucher->id,
                        'amount' => $totalNominal,
                        'payment_date' => Carbon::parse($request->voucher_date),
                    ]);
                    $previousAmount = 0;

                    // Update informasi invoice berdasarkan total pembayaran terkait
                    $totalPaid = $invoice->payment_vouchers()->sum('amount');
                    $remainingBalance = $invoice->total_amount - $totalPaid;

                    $invoice->update([
                        'remaining_amount' => max(0, $remainingBalance),
                        'status' => $remainingBalance <= 0 ? 'paid' : 'pending',
                    ]);
                } elseif (!$invoice && $generalPayment) {
                    // Kasus 4: Voucher tidak ada di invoices, tetapi ada di invoice_payments.
                    //         Update data invoice_payments saja.
                    $previousAmount = $generalPayment->amount;
                    $generalPayment->update([
                        'amount' => $totalNominal,
                        'payment_date' => Carbon::parse($request->voucher_date),
                        'updated_at' => now(),
                    ]);
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
                'invoice' => $request->invoice,
                'store' => $request->store,
                'total_nominal' => $totalNominal,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Update transactions using relationship
            $voucher->transactions()->delete();
            $transactions = array_filter($request->transactions, function ($transaction) {
                return !empty($transaction['description']) || ($transaction['quantity'] ?? 0) > 0 || ($transaction['nominal'] ?? 0) > 0;
            });
            foreach ($transactions as $transaction) {
                $voucher->transactions()->create([
                    'description' => $transaction['description'] ?? null,
                    'quantity' => $transaction['quantity'] ?? 1,
                    'nominal' => $transaction['nominal'] ?? 0,
                ]);
            }

            // Update voucher details using relationship
            $voucher->voucherDetails()->delete();
            foreach ($request->voucher_details as $detail) {
                $voucher->voucherDetails()->create([
                    'account_code' => $detail['account_code'],
                    'account_name' => $detail['account_name'],
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                ]);
            }

            DB::commit();
            return redirect()->route('voucher_page')->with('success', 'Voucher berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating voucher', [
                'voucher_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['_token', '_method']),
            ]);
            return redirect()->back()->withInput()->withErrors(['message' => 'Gagal memperbarui voucher: ' . $e->getMessage()]);
        }
    }

    public function voucher_delete($id)
    {
        try {
            DB::beginTransaction();

            // 1. Find the voucher to be deleted
            $voucherToDelete = Voucher::findOrFail($id);

            // 2. Delete related voucher details
            $voucherToDelete->voucherDetails()->delete();

            // 3. Get related transactions before deleting them
            $transactions = $voucherToDelete->transactions()->get();

            // 4. Update stock quantities and delete stock if quantity = 0 and no related transactions
            // Group transactions by description to accumulate quantities
            $transactionQuantities = $transactions->groupBy('description')->map(function ($group) {
                return $group->sum('quantity');
            });

            // Update stock quantities for each unique description
            foreach ($transactionQuantities as $description => $totalQuantity) {
                // Find stock record where stocks.item matches transactions.description
                $stock = Stock::where('item', $description)->first();

                if ($stock && $totalQuantity > 0) {
                    // Adjust stock quantity based on voucher type
                    if ($voucherToDelete->voucher_type === 'PJ') {
                        // Increase stock quantity for sales (undo stock reduction)
                        $stock->quantity += $totalQuantity;
                    } elseif ($voucherToDelete->voucher_type === 'PB') {
                        // Decrease stock quantity for purchases (undo stock addition)
                        $stock->quantity -= $totalQuantity;
                        // Ensure quantity doesn't go negative
                        if ($stock->quantity < 0) {
                            $stock->quantity = 0;
                        }
                    }
                    $stock->save();

                    // Check if stock quantity is 0 and no other transactions exist for this item
                    if ($stock->quantity == 0) {
                        $remainingTransactions = DB::table('transactions')
                            ->where('description', $description)
                            ->where('voucher_id', '!=', $voucherToDelete->id) // Exclude current voucher's transactions
                            ->count();
                        if ($remainingTransactions == 0) {
                            $stock->delete();
                        }
                    }
                }
            }

            // 5. Delete related transactions
            $voucherToDelete->transactions()->delete();

            // 6. Handle invoice payments deletion and related vouchers
            if ($voucherToDelete->invoice) {
                $invoice = Invoice::where('invoice', $voucherToDelete->invoice)->first();

                if ($invoice) {
                    // 6.1 Delete invoice payments related to the voucher being deleted
                    $invoice->payment_vouchers()->where('voucher_id', $voucherToDelete->id)->delete();

                    // 6.2 Find other vouchers that have invoice_payments related to the same invoice
                    $relatedVouchersToDelete = Voucher::whereHas('invoice_payments', function ($query) use ($invoice) {
                        $query->where('invoice_id', $invoice->id);
                    })
                        ->where('id', '!=', $voucherToDelete->id) // Exclude the voucher being deleted
                        ->get();

                    // 6.3 Delete the related vouchers
                    foreach ($relatedVouchersToDelete as $relatedVoucher) {
                        // Delete invoice payments associated with the related voucher
                        InvoicePayment::where('voucher_id', $relatedVoucher->id)->delete();
                        $relatedVoucher->delete();
                    }

                    // 6.4 Check if the invoice has any remaining payments. If not, delete the invoice
                    $remainingPayments = InvoicePayment::where('invoice_id', $invoice->id)->count();
                    if ($remainingPayments == 0) {
                        $invoice->delete();
                    }
                }
            }
            // 7. Delete the voucher itself
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
