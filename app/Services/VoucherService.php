<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherDetails;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Transactions;
use App\Models\InvoicePayment;
use App\Models\Stock;
use App\Models\Subsidiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class VoucherService
{
    protected $dashboardService;

    /**
     * Constructor with DashboardService dependency
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Prepare data for the voucher page
     *
     * @param Request $request
     * @return array
     */
    public function prepareVoucherPageData(Request $request): array
    {
        $company = Company::select('company_name', 'director')->first();
        $query = Voucher::query();

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('voucher_number', 'like', '%' . $request->search . '%')
                    ->orWhere('invoice', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('voucher_type') && $request->voucher_type != '') {
            $query->where('voucher_type', $request->voucher_type);
        }

        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $query->whereYear('voucher_date', $request->year);
        }

        // Add sorting by created_at in descending order to show latest vouchers first
        $query->orderBy('created_at', 'desc');

        $vouchers = $query->with(['invoices', 'invoice_payments', 'transactions'])
            ->paginate(10)
            ->appends($request->query());

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

        $vouchers->getCollection()->transform(function ($voucher) use ($openingStockTransactions) {
            $hasStock = $voucher->transactions()->where(function ($query) {
                $query->whereHas('stock', function ($subQuery) {
                    $subQuery->whereColumn('transactions.description', 'stocks.item');
                });
            })->exists();
            $voucher->has_stock = $hasStock;

            $voucher->is_opening_stock = isset($openingStockTransactions[$voucher->id])
                ? array_values(array_unique($openingStockTransactions[$voucher->id]))
                : [];
            return $voucher;
        });

        $transactionsData = DB::table('transactions')
            ->select('transactions.description', 'transactions.size', 'transactions.quantity', 'transactions.nominal')
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->whereIn('vouchers.voucher_type', ['PB'])
            ->get()
            ->map(function ($transaction) {
                return [
                    'description' => $transaction->description,
                    'size' => $transaction->size,
                    'quantity' => $transaction->quantity,
                    'nominal' => $transaction->nominal,
                ];
            })->values()->toArray();

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
        $sessionData = DB::table('sessions')
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

        return compact(
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
            'transactionsData',
            'recipes'
        );
    }

    /**
     * Update stock in stocks table
     *
     * @param string $item
     * @param float $quantity
     * @param string $voucherType
     * @param ?string $size
     * @return void
     * @throws \Exception
     */
    public function updateStock(string $item, float $quantity, string $voucherType, ?string $size = null): void
    {
        if (str_starts_with($item, 'HPP ')) {
            return;
        }

        if ($voucherType === 'PB' && is_null($size)) {
            throw new \Exception("Ukuran wajib diisi untuk item {$item} pada voucher tipe PB.");
        }

        $stock = Stock::where('item', $item)->where('size', $size)->first();

        if ($voucherType === 'PB') {
            if ($stock) {
                $stock->quantity += $quantity;
                $stock->save();
            } else {
                Stock::create([
                    'item' => $item,
                    'size' => $size,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Stock::create([
                    'item' => "HPP {$item}",
                    'size' => $size,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } elseif ($voucherType === 'RPB') {
            if (!$stock) {
                throw new \Exception("Stok untuk item {$item} dengan ukuran {$size} tidak ditemukan di tabel stocks.");
            }
            $stock->quantity -= $quantity;
            $stock->save();

            if ($stock->quantity < 0) {
                throw new \Exception("Stok untuk item {$item} dengan ukuran {$size} tidak mencukupi di tabel stocks.");
            }
        } elseif ($voucherType === 'RPJ') {
            if ($stock) {
                $stock->quantity += $quantity;
                $stock->save();
            } else {
                Stock::create([
                    'item' => $item,
                    'size' => $size,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    /**
     * Update adjustment stock across stock tables
     *
     * @param string $item
     * @param ?string $size
     * @param float $quantity
     * @param bool $isIncrease
     * @return void
     * @throws \Exception
     */
    private function updateAdjustmentStock(string $item, ?string $size, float $quantity, bool $isIncrease = true): void
    {
        if (str_starts_with($item, 'HPP ')) {
            return;
        }

        $modelPriorities = [Stock::class];

        $updated = false;

        foreach ($modelPriorities as $model) {
            $stock = $model::where('item', $item)->where('size', $size)->first();
            if ($stock) {
                if ($isIncrease) {
                    $stock->quantity += $quantity;
                } else {
                    if ($stock->quantity < $quantity) {
                        throw new \Exception("Stok tidak mencukupi untuk item {$item} dengan ukuran {$size} di tabel " . (new \ReflectionClass($model))->getShortName() . ". Tersedia: {$stock->quantity}, Dibutuhkan: {$quantity}.");
                    }
                    $stock->quantity -= $quantity;
                }
                $stock->save();
                $updated = true;
                break;
            }
        }

        if (!$updated && $isIncrease) {
            // Create in Stock if not found
            Stock::create([
                'item' => $item,
                'size' => $size,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (!$updated) {
            throw new \Exception("Stok untuk item {$item} dengan ukuran {$size} tidak ditemukan di tabel manapun.");
        }
    }

    /**
     * Reverse stock across all stock tables
     *
     * @param string $item
     * @param float $quantity
     * @param string $voucherType
     * @param ?string $size
     * @param ?int $recipeId
     * @return void
     * @throws \Exception
     */
    private function reverseStock(string $item, float $quantity, string $voucherType, ?string $size = null, ?int $recipeId = null): void
    {
        if (str_starts_with($item, 'HPP ')) {
            return; // Skip direct HPP items as they are not stored in transactions for PJ
        }

        if ($voucherType === 'PYB' || $voucherType === 'PYK') {
            // For PYB reverse: subtract (isIncrease = false), for PYK reverse: add (isIncrease = true)
            $isIncrease = ($voucherType === 'PYK');
            $this->updateAdjustmentStock($item, $size, $quantity, $isIncrease);
            return;
        }

        if ($voucherType === 'PB') {
            $stock = Stock::where('item', $item)->where('size', $size)->first();
            if ($stock) {
                $stock->quantity -= $quantity;
                $stock->save();
                if ($stock->quantity < 0) {
                    throw new \Exception("Stok untuk item {$item} dengan ukuran {$size} tidak mencukupi setelah pembalikan di tabel stocks.");
                }
            }
        } elseif ($voucherType === 'PJ') {
            // Reverse both non-HPP and HPP stock
            $items = [$item, "HPP {$item}"];
            foreach ($items as $currentItem) {
                $stocks = Stock::where('item', $currentItem)->where('size', $size)->first();
                $remainingQuantity = $quantity;
                if ($stocks && $remainingQuantity > 0) {
                    $add = min($stocks->quantity + $quantity, $quantity);
                    $stocks->quantity += $add;
                    $stocks->save();
                    $remainingQuantity -= $add;
                }
            }
        } elseif ($voucherType === 'RPB') {
            $stock = Stock::where('item', $item)->where('size', $size)->first();
            if ($stock) {
                $stock->quantity += $quantity;
                $stock->save();
            }
        } elseif ($voucherType === 'RPJ') {
            // Reverse RPJ: reduce stock since original RPJ increases it
            $stock = Stock::where('item', $item)->where('size', $size)->first();
            if ($stock) {
                $stock->quantity -= $quantity;
                $stock->save();
                if ($stock->quantity < 0) {
                    throw new \Exception("Stok untuk item {$item} dengan ukuran {$size} tidak mencukupi setelah pembalikan di tabel stocks.");
                }
            }
            // Also reverse HPP stock
            $hppStock = Stock::where('item', "HPP {$item}")->where('size', $size)->first();
            if ($hppStock) {
                $hppStock->quantity -= $quantity;
                $hppStock->save();
                if ($hppStock->quantity < 0) {
                    throw new \Exception("Stok untuk item HPP {$item} dengan ukuran {$size} tidak mencukupi setelah pembalikan di tabel stocks.");
                }
            }
        }
    }

    /**
     * Create a new voucher
     *
     * @param Request $request
     * @param string $voucherNumber
     * @return Voucher
     * @throws \Exception
     */
    public function createVoucher(Request $request, string $voucherNumber): Voucher
    {
        DB::beginTransaction();
        try {
            Log::info('Voucher request data:', $request->all());

            config(['app.timezone' => 'Asia/Jakarta']);
            $voucherDetailsData = collect($request->voucher_details ?? [])
                ->filter(function ($detail) {
                    return !empty($detail['account_code']);
                })
                ->values()
                ->toArray();

            $voucherData = [
                'voucher_number' => $voucherNumber,
                'voucher_type' => $request->voucher_type,
                'voucher_date' => Carbon::parse($request->voucher_date)->setTimezone('Asia/Jakarta'),
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

            $voucher = Voucher::create($voucherData);
            Log::info('Voucher created:', ['voucher_id' => $voucher->id, 'voucher_number' => $voucherNumber, 'voucher_type' => $request->voucher_type]);

            $transactionsToCreate = [];
            $hppStockUpdates = [];
            // Handle both 'recipe_id' and 'recipe' for compatibility
            $recipeId = $request->recipe_id ?? $request->recipe ?? null;
            if ($request->has('transactions') && is_array($request->transactions)) {
                Log::info('Processing non-recipe transactions for voucher:', ['voucher_id' => $voucher->id]);
                foreach ($request->transactions as $index => $transaction) {
                    if (!empty($transaction['description']) && isset($transaction['quantity'])) {
                        $quantity = floatval($transaction['quantity'] ?? 1);
                        if ($quantity <= 0) {
                            Log::error('Invalid transaction quantity:', ['voucher_id' => $voucher->id, 'description' => $transaction['description'], 'quantity' => $quantity]);
                            throw new \Exception("Kuantitas tidak valid untuk transaksi: {$transaction['description']}.");
                        }
                        $isHpp = str_starts_with($transaction['description'], 'HPP ');

                        // Add all transactions (including HPP for PJ) to $transactionsToCreate
                        $transactionsToCreate[] = [
                            'voucher_id' => $voucher->id,
                            'description' => $transaction['description'],
                            'quantity' => $quantity,
                            'size' => $transaction['size'] ?? null,
                            'nominal' => floatval($transaction['nominal'] ?? 0.00),
                            'is_hpp' => $isHpp,
                            'index' => $index,
                        ];

                        // Keep HPP transactions in $hppStockUpdates for PJ voucher stock updates
                        if ($request->voucher_type === 'PJ' && $isHpp) {
                            $hppStockUpdates[] = [
                                'description' => $transaction['description'],
                                'quantity' => $quantity,
                                'size' => $transaction['size'] ?? null,
                                'index' => $index,
                            ];
                        }
                    }
                }
            } else {
                Log::info('No non-recipe transactions provided for voucher:', ['voucher_id' => $voucher->id]);
                throw new \Exception('Tidak ada transaksi valid atau kondisi PK tidak terpenuhi.');
            }

            // Create transaction records
            foreach ($transactionsToCreate as $transaction) {
                Transactions::create([
                    'voucher_id' => $voucher->id,
                    'description' => $transaction['description'],
                    'size' => $transaction['size'] ?? null,
                    'quantity' => $transaction['quantity'],
                    'nominal' => $transaction['nominal'],
                ]);
                Log::info('Transaction created:', [
                    'voucher_id' => $voucher->id,
                    'description' => $transaction['description'],
                    'size' => $transaction['size'],
                    'quantity' => $transaction['quantity'],
                    'nominal' => $transaction['nominal'],
                ]);
            }

            if (in_array($request->voucher_type, ['PB', 'PJ', 'RPB', 'RPJ']) && (!empty($transactionsToCreate) || !empty($hppStockUpdates))) {
                if ($request->voucher_type === 'PJ') {
                    foreach ($transactionsToCreate as $transaction) {
                        $item = $transaction['description'];
                        $quantity = $transaction['quantity'];
                        $size = $transaction['size'] ?? null;

                        $hppItem = collect($hppStockUpdates)->firstWhere(function ($hpp) use ($item, $size) {
                            return $hpp['description'] === "HPP {$item}" && $hpp['size'] === $size;
                        });

                        // if (!$hppItem) {
                        //     throw new \Exception("Transaksi HPP untuk item {$item} dengan ukuran {$size} tidak ditemukan.");
                        // }
                        // if ($hppItem['quantity'] != $quantity) {
                        //     throw new \Exception("Kuantitas HPP untuk item {$item} dengan ukuran {$size} tidak sesuai dengan kuantitas stok.");
                        // }
                    }
                } elseif ($request->voucher_type === 'RPJ') {
                    foreach ($transactionsToCreate as $transaction) {
                        $item = $transaction['description'];
                        $quantity = $transaction['quantity'];
                        $size = $transaction['size'] ?? null;

                        $hppItem = collect($hppStockUpdates)->firstWhere(function ($hpp) use ($item, $size) {
                            return $hpp['description'] === "HPP {$item}" && $hpp['size'] === $size;
                        });

                        $this->updateStock($item, $quantity, 'RPJ', $size);
                        $this->updateStock("HPP {$item}", $quantity, 'RPJ', $size);
                    }
                } else {
                    foreach ($transactionsToCreate as $transaction) {
                        if (!$transaction['is_hpp']) {
                            $item = $transaction['description'];
                            $quantity = $transaction['quantity'];
                            $size = $transaction['size'] ?? null;

                            if ($request->voucher_type === 'PB') {
                                $this->updateStock($item, $quantity, 'PB', $size);
                            } elseif ($request->voucher_type === 'RPB') {
                                $this->updateStock($item, $quantity, 'RPB', $size);
                            }
                        }
                    }
                }
            } elseif (in_array($request->voucher_type, ['PYB', 'PYK'])) {
                foreach ($transactionsToCreate as $transaction) {
                    if (!$transaction['is_hpp']) {
                        $item = $transaction['description'];
                        $quantity = $transaction['quantity'];
                        $size = $transaction['size'] ?? null;
                        $isIncrease = $request->voucher_type === 'PYB';
                        $this->updateAdjustmentStock($item, $size, $quantity, $isIncrease);
                    }
                }
            }

            foreach ($voucherDetailsData as $detail) {
                VoucherDetails::create([
                    'voucher_id' => $voucher->id,
                    'account_code' => $detail['account_code'],
                    'account_name' => $detail['account_name'] ?? null,
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                ]);
            }

            if ($request->use_invoice === 'yes') {
                $subsidiaryCode = collect($voucherDetailsData)->firstWhere(function ($detail) {
                    return Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                })['account_code'] ?? null;

                if (!$subsidiaryCode) {
                    throw new \Exception('Tidak ditemukan kode subsidiary yang valid di rincian voucher.');
                }

                $voucherDetails = DB::table('voucher_details')
                    ->where('voucher_id', $voucher->id)
                    ->where('account_code', $subsidiaryCode)
                    ->selectRaw('SUM(debit) - SUM(credit) as total_amount')
                    ->first();

                if (!$voucherDetails || is_null($voucherDetails->total_amount)) {
                    throw new \Exception("Tidak ditemukan rincian voucher untuk kode subsidiary {$subsidiaryCode} dan voucher_id {$voucher->id}, atau total_amount null.");
                }

                $totalAmount = abs($voucherDetails->total_amount);

                if ($totalAmount <= 0) {
                    throw new \Exception("Jumlah total untuk kode subsidiary {$subsidiaryCode} dan voucher_id {$voucher->id} tidak valid atau nol.");
                }

                $invoice = Invoice::where('invoice', $request->invoice)->first();

                if ($request->use_existing_invoice === 'yes' && !$invoice) {
                    throw new \Exception('Invoice yang dipilih tidak ditemukan.');
                }

                if (!$invoice) {
                    Invoice::create([
                        'invoice' => $request->invoice,
                        'voucher_number' => $voucher->voucher_number,
                        'subsidiary_code' => $subsidiaryCode,
                        'status' => 'pending',
                        'due_date' => Carbon::parse($request->dueDate)->setTimezone('Asia/Jakarta'),
                        'total_amount' => $totalAmount,
                        'remaining_amount' => $totalAmount,
                    ]);
                } else {
                    $invoice->remaining_amount -= $totalAmount;
                    $invoice->status = $invoice->remaining_amount <= 0 ? 'paid' : 'pending';
                    $invoice->save();

                    $invoice->invoice_payments()->create([
                        'voucher_id' => $voucher->id,
                        'amount' => $totalAmount,
                        'payment_date' => Carbon::parse($request->voucher_date)->setTimezone('Asia/Jakarta'),
                    ]);
                }
            }
            // Clear cache for the voucher's period
            $this->dashboardService->clearCacheForPeriod(
                $voucher->voucher_date->year,
                $voucher->voucher_date->month
            );

            DB::commit();
            Log::info('Voucher creation completed successfully:', ['voucher_id' => $voucher->id]);
            return $voucher;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating voucher:', [
                'voucher_number' => $voucherNumber,
                'voucher_type' => $request->voucher_type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get invoice details
     *
     * @param string $invoiceNumber
     * @return array
     * @throws \Exception
     */
    public function getInvoiceDetails(string $invoiceNumber): array
    {
        $invoice = Invoice::where('invoice', $invoiceNumber)->first();
        if ($invoice) {
            return [
                'total_amount' => $invoice->total_amount,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
            ];
        }
        throw new \Exception('Invoice tidak ditemukan');
    }

    /**
     * Prepare data for editing a voucher
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function prepareVoucherEditData(int $id): array
    {
        $company = Company::select('company_name', 'director')->firstOrFail();
        $voucher = Voucher::with(['voucherDetails', 'transactions'])->findOrFail($id);

        // Validasi voucher->created_at
        if (!$voucher->created_at) {
            Log::warning('Voucher created_at is null for voucher ID: ' . $id);
            $voucherCreatedAt = now()->toIso8601String();
        } else {
            $voucherCreatedAt = $voucher->created_at->toIso8601String();
        }

        $accounts = ChartOfAccount::orderBy('account_code')->get();
        if ($accounts->isEmpty()) {
            throw new \Exception('Tidak ada chart of accounts yang ditemukan.');
        }
        $accountsData = $accounts->map(function ($account) {
            return [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
            ];
        })->values()->toArray();

        $existingInvoices = Invoice::pluck('invoice')->unique()->values()->toArray();

        $storeNames = Subsidiary::pluck('store_name')->unique()->values()->toArray();
        if (empty($storeNames)) {
            $storeNames = [];
        }

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

        $stocks = Stock::select('item', 'size', 'quantity')
            ->orderBy('item')
            ->get()
            ->map(function ($stock) {
                return [
                    'item' => $stock->item,
                    'size' => $stock->size,
                    'quantity' => $stock->quantity,
                ];
            })->values()->toArray();
        if (empty($stocks)) {
            $stocks = [];
        }

        // Prepare transactionsData based on voucher type
        $transactionsData = [];
        if ($voucher->voucher_type === 'PJ' || $voucher->voucher_type === 'RPJ') {
            // Fetch historical purchase transactions for PJ vouchers
            $historicalTransactions = Transactions::whereHas('voucher', function ($query) use ($voucher) {
                $query->whereIn('voucher_type', ['PB'])
                    ->where('created_at', '<', $voucher->created_at);
            })
                ->whereNotNull('description')
                ->whereNotNull('size')
                ->whereNotNull('nominal')
                ->whereNotNull('created_at')
                ->select('description', 'size', 'quantity', 'nominal', 'created_at')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'description' => $transaction->description,
                        'size' => $transaction->size,
                        'quantity' => $transaction->quantity,
                        'nominal' => number_format($transaction->nominal, 2, '.', ''),
                        'total' => number_format($transaction->quantity * ($transaction->nominal ?? 0), 2, '.', ''),
                        'created_at' => $transaction->created_at ? $transaction->created_at->toIso8601String() : now()->toIso8601String(),
                    ];
                })->toArray();

            // Current transactions (exclude those with same created_at as voucher)
            $currentTransactions = $voucher->transactions
                ->filter(function ($transaction) use ($voucher) {
                    return $transaction->created_at->toIso8601String() !== $voucher->created_at->toIso8601String();
                })
                ->map(function ($transaction) {
                    return [
                        'description' => $transaction->description,
                        'size' => $transaction->size,
                        'quantity' => $transaction->quantity,
                        'nominal' => number_format($transaction->nominal, 2, '.', ''),
                        'total' => number_format($transaction->quantity * ($transaction->nominal ?? 0), 2, '.', ''),
                        'created_at' => $transaction->created_at ? $transaction->created_at->toIso8601String() : now()->toIso8601String(),
                    ];
                })->values()->toArray();

            $transactionsData = array_merge($historicalTransactions, $currentTransactions);
        } else {
            // For other voucher types, use current voucher's transactions
            $transactionsData = $voucher->transactions->map(function ($transaction) {
                return [
                    'description' => $transaction->description,
                    'size' => $transaction->size,
                    'quantity' => $transaction->quantity,
                    'nominal' => number_format($transaction->nominal ?? 0, 2, '.', ''),
                    'total' => number_format($transaction->quantity * ($transaction->nominal ?? 0), 2, '.', ''),
                    'created_at' => $transaction->created_at ? $transaction->created_at->toIso8601String() : now()->toIso8601String(),
                ];
            })->values()->toArray();
        }

        $headingText = $this->getVoucherHeading($voucher->voucher_type);

        $dueDate = '';
        if ($voucher->invoice) {
            $invoice = Invoice::where('invoice', $voucher->invoice)->first();
            if ($invoice && $invoice->due_date) {
                $dueDate = \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d');
            }
        }

        return compact(
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
            'dueDate',
            'recipes',
            'voucherCreatedAt'
        );
    }

    /**
     * Update an existing voucher
     *
     * @param Request $request
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function updateVoucher(Request $request, int $id): void
    {
        DB::beginTransaction();
        try {
            config(['app.timezone' => 'Asia/Jakarta']);
            $voucherDetailsData = collect($request->voucher_details ?? [])
                ->filter(function ($detail) {
                    return !empty($detail['account_code']);
                })->values()->toArray();

            $transactionsData = collect($request->transactions ?? [])
                ->filter(function ($transaction) {
                    $hasDescription = !empty($transaction['description']);
                    $isHpp = str_starts_with($transaction['description'], 'HPP ');
                    $hasQuantity = isset($transaction['quantity']) && floatval($transaction['quantity']) > 0;
                    $hasNominal = isset($transaction['nominal']) && floatval($transaction['nominal']) >= 0;
                    return $hasDescription && ($isHpp || ($hasQuantity && $hasNominal));
                })->values()->toArray();

            $voucher = Voucher::with(['transactions', 'voucherDetails'])->findOrFail($id);

            $totalNominal = 0;
            $transactionItems = [];
            $hppStockUpdates = [];
            $recipeId = $request->recipe_id ?? null;

            if (!empty($transactionsData)) {
                foreach ($transactionsData as $index => $transaction) {
                    $quantity = floatval($transaction['quantity']);
                    $nominal = floatval($transaction['nominal']);
                    $totalNominal += $quantity * $nominal;
                    $isHpp = str_starts_with($transaction['description'], 'HPP ');
                    $transactionItems[$index] = [
                        'description' => $transaction['description'],
                        'size' => $transaction['size'] ?? null,
                        'quantity' => $quantity,
                        'nominal' => $nominal,
                        'is_hpp' => $isHpp,
                    ];
                    if ($request->voucher_type === 'PJ' && $isHpp) {
                        $hppStockUpdates[$index] = [
                            'description' => $transaction['description'],
                            'size' => $transaction['size'] ?? null,
                            'quantity' => $quantity,
                            'index' => $index,
                        ];
                    }
                }
            }

            $totalDebit = 0;
            $totalCredit = 0;
            $subsidiaryCount = 0;
            foreach ($voucherDetailsData as $detail) {
                $debit = floatval($detail['debit'] ?? 0);
                $credit = floatval($detail['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    throw new \Exception("Debit dan kredit tidak boleh keduanya diisi untuk kode akun: {$detail['account_code']}");
                }

                $totalDebit += $debit;
                $totalCredit += $credit;

                if ($request->use_invoice === 'yes') {
                    $isSubsidiary = Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                    if ($isSubsidiary) {
                        $subsidiaryCount++;
                    }
                    if ($subsidiaryCount > 1) {
                        throw new \Exception("Hanya satu kode subsidiary yang boleh digunakan saat invoice diaktifkan.");
                    }
                }
            }

            if (round($totalNominal, 2) !== round($totalDebit, 2) || round($totalNominal, 2) !== round($totalCredit, 2)) {
                throw new \Exception('Total nominal harus sama dengan total debit dan total kredit.');
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \Exception('Total debit harus sama dengan total kredit.');
            }

            if ($request->voucher_type === 'PB') {
                $stockItems = collect($transactionItems)
                    ->filter(function ($item) {
                        return !$item['is_hpp'];
                    })->pluck('description')->toArray();
                foreach ($transactionItems as $index => $item) {
                    if ($item['is_hpp']) {
                        $stockItem = str_replace('HPP ', '', $item['description']);
                        // if (!in_array($stockItem, $stockItems)) {
                        //     throw new \Exception("Transaksi HPP untuk item {$stockItem} tidak memiliki transaksi stok yang sesuai.");
                        // }
                        $stockIndex = array_search($stockItem, array_column($transactionItems, 'description'));
                        // if ($stockIndex !== false && $transactionItems[$stockIndex]['quantity'] != $item['quantity']) {
                        //     throw new \Exception("Kuantitas HPP untuk item {$stockItem} tidak sesuai dengan kuantitas stok.");
                        // }
                        // if ($transactionItems[$stockIndex]['size'] != $item['size']) {
                        //     throw new \Exception("Ukuran HPP untuk item {$stockItem} tidak sesuai dengan ukuran stok.");
                        // }
                    }
                }
                $nonHppCount = collect($transactionItems)->filter(function ($item) {
                    return !$item['is_hpp'];
                })->count();
                if ($nonHppCount === 0) {
                    throw new \Exception('Voucher Pembelian harus memiliki setidaknya satu transaksi non-HPP.');
                }
            }

            if ($request->voucher_type === 'RPB') {
                foreach ($transactionItems as $transaction) {
                    if (!str_starts_with($transaction['description'], 'HPP ')) {
                        $item = $transaction['description'];
                        $quantity = floatval($transaction['quantity']);
                        $size = $transaction['size'] ?? null;
                        $stock = Stock::where('item', $item)->where('size', $size)->first();
                        if (!$stock || $stock->quantity < $quantity) {
                            throw new \Exception("Stok untuk item {$item} dengan ukuran {$size} tidak mencukupi di tabel stocks. Tersedia: " . ($stock ? $stock->quantity : 0) . ", Dibutuhkan: {$quantity}.");
                        }
                    }
                }
            } elseif ($request->voucher_type === 'PJ' || $request->voucher_type === 'RPJ') {
                foreach ($transactionItems as $transaction) {
                    $item = $transaction['description'];
                    $quantity = $transaction['quantity'];
                    $size = $transaction['size'] ?? null;

                    $hppItem = collect($hppStockUpdates)->firstWhere(function ($hpp) use ($item, $size) {
                        return $hpp['description'] === "HPP {$item}" && $hpp['size'] === $size;
                    });
                }
            } elseif (in_array($request->voucher_type, ['PYB', 'PYK'])) {
                foreach ($transactionItems as $transaction) {
                    $item = $transaction['description'];
                    $quantity = $transaction['quantity'];
                    $size = $transaction['size'] ?? null;
                    $isIncrease = $request->voucher_type === 'PYB';

                    // Validate for PYK
                    if (!$isIncrease) {
                        $found = false;
                        $modelPriorities = [Stock::class];
                        foreach ($modelPriorities as $model) {
                            $stock = $model::where('item', $item)->where('size', $size)->first();
                            if ($stock && $stock->quantity >= $quantity) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            throw new \Exception("Stok tidak mencukupi untuk item {$item} dengan ukuran {$size} di tabel manapun untuk PYK.");
                        }
                    }
                }
            }

            if (in_array($voucher->voucher_type, ['PB', 'PJ', 'PYB', 'PYK', 'RPB', 'RPJ'])) {
                foreach ($voucher->transactions as $transaction) {
                    if (!str_starts_with($transaction->description, 'HPP ')) {
                        $this->reverseStock(
                            $transaction->description,
                            floatval($transaction->quantity),
                            $voucher->voucher_type,
                            $transaction->size,
                            $recipeId
                        );
                    }
                }
            }

            if (in_array($request->voucher_type, ['PB', 'PJ', 'PYB', 'PYK', 'RPB', 'RPJ'])) {
                if ($request->voucher_type === 'PJ') {
                    foreach ($transactionItems as $transaction) {
                        $item = $transaction['description'];
                        $quantity = $transaction['quantity'];
                        $size = $transaction['size'] ?? null;
                    }
                } elseif ($request->voucher_type === 'RPJ') {
                    foreach ($transactionItems as $transaction) {
                        if (!$transaction['is_hpp']) {
                            $item = $transaction['description'];
                            $quantity = $transaction['quantity'];
                            $size = $transaction['size'] ?? null;
                            $this->updateStock($item, $quantity, 'RPJ', $size);
                            $this->updateStock("HPP {$item}", $quantity, 'RPJ', $size);
                        }
                    }
                } elseif (in_array($request->voucher_type, ['PYB', 'PYK'])) {
                    foreach ($transactionItems as $transaction) {
                        $item = $transaction['description'];
                        $quantity = $transaction['quantity'];
                        $size = $transaction['size'] ?? null;
                        $isIncrease = $request->voucher_type === 'PYB';
                        $this->updateAdjustmentStock($item, $size, $quantity, $isIncrease);
                    }
                } else {
                    foreach ($transactionItems as $transaction) {
                        if (!$transaction['is_hpp']) {
                            $item = $transaction['description'];
                            $quantity = $transaction['quantity'];
                            $size = $transaction['size'] ?? null;

                            if ($request->voucher_type === 'PB') {
                                $this->updateStock($item, $quantity, 'PB', $size);
                            } elseif ($request->voucher_type === 'RPB') {
                                $this->updateStock($item, $quantity, 'RPB', $size);
                            }
                        }
                    }
                }
            }

            if ($request->use_invoice === 'yes') {
                $subsidiaryCode = collect($voucherDetailsData)->firstWhere(function ($detail) {
                    return Subsidiary::where('subsidiary_code', $detail['account_code'])->exists();
                })['account_code'] ?? null;

                if (!$subsidiaryCode) {
                    throw new \Exception('Tidak ditemukan kode subsidiary yang valid di rincian voucher.');
                }

                $totalAmount = abs(DB::table('voucher_details')
                    ->where('voucher_id', $voucher->id)
                    ->where('account_code', $subsidiaryCode)
                    ->selectRaw('SUM(debit) - SUM(credit) as total_amount')
                    ->first()->total_amount ?? 0);

                if ($totalAmount <= 0) {
                    throw new \Exception("Jumlah total untuk kode subsidiary {$subsidiaryCode} tidak valid atau nol.");
                }

                $invoice = $request->use_existing_invoice === 'yes'
                    ? Invoice::where('invoice', $request->invoice)->firstOrFail()
                    : Invoice::create([
                        'invoice' => $request->invoice,
                        'voucher_number' => $voucher->voucher_number,
                        'subsidiary_code' => $subsidiaryCode,
                        'status' => 'pending',
                        'due_date' => Carbon::parse($request->due_date)->setTimezone('Asia/Jakarta'),
                        'total_amount' => $totalAmount,
                        'remaining_amount' => $totalAmount,
                    ]);

                $payment = InvoicePayment::where('voucher_id', $voucher->id)->first();
                if ($payment) {
                    $invoice->remaining_amount += $payment->amount;
                    $payment->update([
                        'invoice_id' => $invoice->id,
                        'amount' => $totalAmount,
                        'payment_date' => Carbon::parse($request->voucher_date)->setTimezone('Asia/Jakarta'),
                    ]);
                } else {
                    InvoicePayment::create([
                        'voucher_id' => $voucher->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $totalAmount,
                        'payment_date' => Carbon::parse($request->voucher_date)->setTimezone('Asia/Jakarta'),
                    ]);
                }
                $invoice->remaining_amount -= $totalAmount;
                $invoice->status = $invoice->remaining_amount <= 0 ? 'paid' : 'pending';
                $invoice->save();

                if ($invoice->remaining_amount < 0) {
                    throw new \Exception("Saldo invoice negatif ({$invoice->remaining_amount}).");
                }
            } else {
                $payment = InvoicePayment::where('voucher_id', $voucher->id)->first();
                if ($payment) {
                    $invoice = Invoice::find($payment->invoice_id);
                    if ($invoice) {
                        $invoice->remaining_amount += $payment->amount;
                        $invoice->save();
                    }
                    $payment->delete();
                }
            }

            $voucher->update([
                'voucher_number' => $request->voucher_number,
                'voucher_type' => $request->voucher_type,
                'voucher_date' => Carbon::parse($request->voucher_date)->setTimezone('Asia/Jakarta'),
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
            ]);

            $voucher->transactions()->delete();
            $transactionsToCreate = $transactionItems;

            foreach ($transactionsToCreate as $transaction) {
                $voucher->transactions()->create([
                    'description' => $transaction['description'],
                    'size' => $transaction['size'] ?? null,
                    'quantity' => floatval($transaction['quantity']),
                    'nominal' => floatval($transaction['nominal']),
                ]);
            }

            $voucher->voucherDetails()->delete();
            foreach ($voucherDetailsData as $detail) {
                if (empty($detail['account_name'])) {
                    $account = ChartOfAccount::where('code', $detail['account_code'])->first();
                    $accountName = $account ? $account->name : 'Unknown Account';
                } else {
                    $accountName = $detail['account_name'];
                }

                $voucher->voucherDetails()->create([
                    'account_code' => $detail['account_code'],
                    'account_name' => $accountName,
                    'debit' => floatval($detail['debit'] ?? 0),
                    'credit' => floatval($detail['credit'] ?? 0),
                ]);
            }
            // Clear cache for the voucher's period
            $this->dashboardService->clearCacheForPeriod(
                $voucher->voucher_date->year,
                $voucher->voucher_date->month
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a voucher and its related data
     *
     * @param int $id
     * @return void
     * @throws \Exception
     */
    public function deleteVoucher(int $id): void
    {
        DB::beginTransaction();
        try {
            $voucherToDelete = Voucher::findOrFail($id);

            if ($voucherToDelete->has_stock) {
                throw new \Exception('Voucher tidak dapat dihapus karena memiliki data stok.');
            }

            $hasInvoices = $voucherToDelete->invoices()->exists();
            $hasInvoiceWithPayments = $voucherToDelete->invoices()->whereHas('invoice_payments')->exists();

            if ($hasInvoices && $hasInvoiceWithPayments) {
                throw new \Exception('Voucher tidak dapat dihapus karena memiliki invoice yang terkait dengan pembayaran.');
            }

            $voucherToDelete->voucherDetails()->delete();

            $transactions = $voucherToDelete->transactions()->get();

            $transactionQuantities = $transactions->groupBy('description')->map(function ($group) {
                return $group->sum('quantity');
            });

            foreach ($transactionQuantities as $description => $totalQuantity) {
                $transaction = $voucherToDelete->transactions()->where('description', $description)->first();

                foreach ([Stock::class] as $model) {
                    $stock = $model::where('item', $description)->first();
                    if ($stock && $stock->quantity == 0) {
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

            $voucherToDelete->transactions()->delete();

            if ($hasInvoices) {
                $invoicePayments = $voucherToDelete->invoice_payments()->get();

                foreach ($invoicePayments as $payment) {
                    $invoice = Invoice::find($payment->invoice_id);
                    if ($invoice) {
                        $invoice->remaining_amount += $payment->amount;
                        $invoice->save();
                    }
                }
                $voucherToDelete->invoice_payments()->delete();

                $invoiceIds = InvoicePayment::where('voucher_id', $voucherToDelete->id)->pluck('invoice_id')->unique();
                foreach ($invoiceIds as $invoiceId) {
                    $remainingPayments = InvoicePayment::where('invoice_id', $invoiceId)->count();
                    if ($remainingPayments == 0) {
                        Invoice::where('id', $invoiceId)->delete();
                    }
                }
            }
            // Clear cache for the voucher's period
            $this->dashboardService->clearCacheForPeriod(
                $voucherToDelete->voucher_date->year,
                $voucherToDelete->voucher_date->month
            );


            $voucherToDelete->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate a voucher number
     *
     * @param string $voucherType
     * @return string
     */
    public function generateVoucherNumber(string $voucherType): string
    {
        // Fetch the last voucher for the given type, ordered by voucher_number
        $lastVoucher = Voucher::where('voucher_type', $voucherType)
            ->orderBy('voucher_number', 'desc')
            ->first();

        $newNumber = 1; // Default starting number
        if ($lastVoucher && !empty($lastVoucher->voucher_number)) {
            // Extract the numeric part after the prefix (e.g., 'RPJ-')
            $prefixLength = strlen($voucherType) + 1; // +1 for the hyphen
            $numericPart = substr($lastVoucher->voucher_number, $prefixLength);

            // Ensure the numeric part is valid
            if (is_numeric($numericPart)) {
                $lastNumber = intval($numericPart);
                $newNumber = $lastNumber + 1;
            } else {
                // Log a warning if the voucher number format is invalid
                Log::warning("Invalid voucher number format for type {$voucherType}: {$lastVoucher->voucher_number}");
            }
        }

        // Generate the new voucher number with proper padding
        return $voucherType . '-' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Prepare data for voucher details view
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function prepareVoucherDetailData(int $id): array
    {
        $company = Company::select('company_name', 'phone', 'director')->firstOrFail();
        $voucher = Voucher::findOrFail($id);
        $voucherDetails = VoucherDetails::where('voucher_id', $id)->get();
        $voucherTransactions = Transactions::where('voucher_id', $id)->get();

        $headingText = $this->getVoucherHeading($voucher->voucher_type);

        return compact('voucher', 'headingText', 'voucherDetails', 'voucherTransactions', 'company');
    }

    /**
     * Prepare data for PDF generation
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function preparePdfData(int $id): array
    {
        $voucher = Voucher::findOrFail($id);
        $details = VoucherDetails::where('voucher_id', $id)->get();
        $transaction = Transactions::where('voucher_id', $id)->get();

        // Fetch stocks and used_stocks data for items in the transaction
        $items = $transaction->pluck('description')->unique()->toArray();

        // Get sizes from stocks
        $stocks = Stock::whereIn('item', $items)
            ->get()
            ->pluck('size', 'item')
            ->toArray();

        // Merge stocks and used_stocks, prioritizing stocks
        $itemSizes = $stocks; // Use stocks takes precedence due to array union

        // Map sizes to transactions
        $transaction = $transaction->map(function ($trans) use ($itemSizes) {
            $trans->size = $itemSizes[$trans->description] ?? null;
            return $trans;
        });

        $companyLogo = Company::value('logo');
        $company = Company::select('company_name', 'phone', 'director', 'email', 'address')->firstOrFail();

        $headingText = $this->getVoucherHeading($voucher->voucher_type);

        return compact('voucher', 'details', 'headingText', 'company', 'transaction', 'companyLogo');
    }

    /**
     * Get voucher heading based on type
     *
     * @param string $voucherType
     * @return string
     */
    private function getVoucherHeading(string $voucherType): string
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
            case 'PYB':
                return 'Penyesuaian Bertambah';
            case 'PYK':
                return 'Penyesuaian Berkurang';
            case 'PYL':
                return 'Penyesuaian Lainnya';
            case 'LN':
                return 'Lainnya';
            case 'RPB':
                return 'Retur Pembelian';
            case 'RPJ':
                return 'Retur Penjualan';
            default:
                return 'Voucher';
        }
    }
}
