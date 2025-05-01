<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subsidiary;
use App\Models\Voucher;
use App\Models\voucherDetails;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubsidiariesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubsidiaryController extends Controller
{
    public function subsidiaryUtang_page(Request $request)
    {
        // Ambil data subsidiaries dengan filter untuk tabel
        $query = Subsidiary::query();

        // Filter berdasarkan nama toko
        if ($request->has('toko') && $request->toko != '') {
            $query->where('store_name', $request->toko);
        }

        // Filter berdasarkan bulan (berdasarkan created_at di subsidiaries)
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('created_at', $request->month);
        }

        // Filter berdasarkan tahun (berdasarkan created_at di subsidiaries)
        if ($request->has('year') && $request->year != '') {
            $query->whereYear('created_at', $request->year);
        }

        // Ambil semua data subsidiaries yang sudah difilter
        $subsidiaries = $query->get();

        // Ambil data vouchers dan voucher_details untuk menghitung total saldo
        $voucherQuery = Voucher::query();

        if ($request->has('month') && $request->month != '') {
            $voucherQuery->whereMonth('voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $voucherQuery->whereYear('voucher_date', $request->year);
        }

        $vouchers = $voucherQuery->get();

        $voucherDetailsQuery = voucherDetails::query()
            ->selectRaw('vouchers.store, voucher_details.account_code, SUM(voucher_details.debit - voucher_details.credit) as total_saldo')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->groupBy('vouchers.store', 'voucher_details.account_code');

        if ($request->has('month') && $request->month != '') {
            $voucherDetailsQuery->whereMonth('vouchers.voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $voucherDetailsQuery->whereYear('vouchers.voucher_date', $request->year);
        }

        $voucherBalances = $voucherDetailsQuery
            ->get()
            ->keyBy(function ($item) {
                return $item->store . '-' . $item->account_code;
            });

        $utangUsaha = $subsidiaries->where('account_code', '2.1.01.01')->map(function ($subsidiary) use ($voucherBalances) {
            $key = $subsidiary->store_name . '-' . $subsidiary->account_code;
            $subsidiary->total_saldo = $voucherBalances->has($key) ? $voucherBalances[$key]->total_saldo : 0;
            return $subsidiary;
        });

        // Kirim data ke view
        return view('subsidiary_utang.subsidiaryUtang_page', compact('subsidiaries', 'utangUsaha'));
    }
    public function subsidiaryUtangDetails(Request $request)
    {
        try {
            $storeName = $request->query('store_name');
            $accountCode = $request->query('account_code');
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');
            $nomorInvoice = $request->query('tipe_voucher');

            $query = Invoice::query()
                ->select(
                    'invoices.id as invoice_id',
                    'invoices.invoice',
                    'invoices.created_at as tanggal',
                    'invoices.total_amount as saldo_awal',
                    'invoices.total_amount as sisa_saldo', // Initial sisa_saldo for invoice row
                    'invoices.voucher_number as nomor_voucher_pembelian',
                    'invoices.remaining_amount',
                    'vouchers.id as voucher_id'
                )
                ->join('subsidiaries', 'invoices.subsidiary_code', '=', 'subsidiaries.subsidiary_code')
                ->leftJoin('vouchers', 'invoices.voucher_number', '=', 'vouchers.voucher_number')
                ->where('subsidiaries.store_name', $storeName)
                ->where('subsidiaries.account_code', $accountCode);

            if ($nomorInvoice) {
                $query->where('invoices.invoice', $nomorInvoice);
            }

            if ($bulan) {
                $query->whereMonth('invoices.created_at', $bulan);
            }

            if ($tahun) {
                $query->whereYear('invoices.created_at', $tahun);
            }

            $invoices = $query->get();

            $transactions = $invoices->map(function ($invoice) {
                // Fetch related invoice_payments, ordered by payment_date
                $payments = InvoicePayment::query()
                    ->select(
                        'invoice_payments.id',
                        'invoice_payments.invoice_id',
                        'invoice_payments.amount as saldo_akhir',
                        'invoice_payments.payment_date as payment_date',
                        'invoice_payments.voucher_id as payment_voucher_id',
                        'vouchers.voucher_number as nomor_voucher_pelunasan'
                    )
                    ->leftJoin('vouchers', 'invoice_payments.voucher_id', '=', 'vouchers.id')
                    ->where('invoice_payments.invoice_id', $invoice->invoice_id)
                    ->orderBy('invoice_payments.payment_date')
                    ->get();

                // Invoice row
                $invoiceRow = [
                    'type' => 'invoice',
                    'invoice_id' => $invoice->invoice_id,
                    'invoice' => $invoice->invoice,
                    'nomor_voucher_pembelian' => $invoice->nomor_voucher_pembelian ?: '-',
                    'nomor_voucher_pelunasan' => '-',
                    'voucher_date' => $invoice->tanggal,
                    'saldo_awal' => $invoice->saldo_awal,
                    'saldo_akhir' => '-',
                    'sisa_saldo' => $invoice->sisa_saldo,
                    'voucher_id' => $invoice->voucher_id ?: null
                ];

                // Payment rows with dynamic saldo_awal and sisa_saldo
                $previousSisaSaldo = $invoice->saldo_awal; // Start with invoice's total_amount
                $paymentRows = $payments->map(function ($payment) use (&$previousSisaSaldo) {
                    // Saldo Awal for this payment is the Sisa Saldo from the previous row
                    $saldoAwal = $previousSisaSaldo;
                    // Sisa Saldo is Saldo Awal - Saldo Akhir for this payment
                    $sisaSaldo = $saldoAwal - $payment->saldo_akhir;
                    // Update previousSisaSaldo for the next iteration
                    $previousSisaSaldo = $sisaSaldo;

                    return [
                        'type' => 'payment',
                        'invoice_id' => $payment->invoice_id,
                        'invoice' => null,
                        'nomor_voucher_pembelian' => '-',
                        'nomor_voucher_pelunasan' => $payment->nomor_voucher_pelunasan ?: '-',
                        'voucher_date' => $payment->payment_date,
                        'saldo_awal' => $saldoAwal,
                        'saldo_akhir' => $payment->saldo_akhir,
                        'sisa_saldo' => $sisaSaldo,
                        'payment_voucher_id' => $payment->payment_voucher_id ?: null
                    ];
                })->toArray();

                return array_merge([$invoiceRow], $paymentRows);
            })->flatten(1);

            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error in subsidiaryUtangDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }
    public function subsidiaryPiutang_page(Request $request)
    {
        // Ambil data subsidiaries dengan filter untuk tabel
        $query = Subsidiary::query();

        // Filter berdasarkan nama toko
        if ($request->has('toko') && $request->toko != '') {
            $query->where('store_name', $request->toko);
        }

        // Filter berdasarkan bulan (berdasarkan created_at di subsidiaries)
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('created_at', $request->month);
        }

        // Filter berdasarkan tahun (berdasarkan created_at di subsidiaries)
        if ($request->has('year') && $request->year != '') {
            $query->whereYear('created_at', $request->year);
        }

        // Ambil semua data subsidiaries yang sudah difilter
        $subsidiaries = $query->with('invoices')->get();

        // Ambil data vouchers dan voucher_details untuk menghitung total saldo
        $voucherQuery = Voucher::query();

        if ($request->has('month') && $request->month != '') {
            $voucherQuery->whereMonth('voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $voucherQuery->whereYear('voucher_date', $request->year);
        }

        $vouchers = $voucherQuery->get();

        $voucherDetailsQuery = voucherDetails::query()
            ->selectRaw('vouchers.store, voucher_details.account_code, SUM(voucher_details.debit - voucher_details.credit) as total_saldo')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->groupBy('vouchers.store', 'voucher_details.account_code');

        if ($request->has('month') && $request->month != '') {
            $voucherDetailsQuery->whereMonth('vouchers.voucher_date', $request->month);
        }

        if ($request->has('year') && $request->year != '') {
            $voucherDetailsQuery->whereYear('vouchers.voucher_date', $request->year);
        }

        $voucherBalances = $voucherDetailsQuery
            ->get()
            ->keyBy(function ($item) {
                return $item->store . '-' . $item->account_code;
            });

        $piutangUsaha = $subsidiaries->where('account_code', '1.1.03.01')->map(function ($subsidiary) use ($voucherBalances) {
            $key = $subsidiary->store_name . '-' . $subsidiary->account_code;
            $subsidiary->total_saldo = $voucherBalances->has($key) ? $voucherBalances[$key]->total_saldo : 0;
            return $subsidiary;
        });

        // Kirim data ke view
        return view('subsidiary_utang.subsidiaryPiutang_page', compact('subsidiaries', 'piutangUsaha'));
    }
    public function subsidiaryPiutangDetails(Request $request)
    {
        try {
            $storeName = $request->query('store_name');
            $accountCode = $request->query('account_code');
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');
            $nomorInvoice = $request->query('tipe_voucher');

            $query = Invoice::query()
                ->select(
                    'invoices.id as invoice_id',
                    'invoices.invoice',
                    'invoices.created_at as tanggal',
                    'invoices.total_amount as saldo_awal',
                    'invoices.total_amount as sisa_saldo', // Initial sisa_saldo for invoice row
                    'invoices.voucher_number as nomor_voucher_pembelian',
                    'invoices.remaining_amount',
                    'vouchers.id as voucher_id'
                )
                ->join('subsidiaries', 'invoices.subsidiary_code', '=', 'subsidiaries.subsidiary_code')
                ->leftJoin('vouchers', 'invoices.voucher_number', '=', 'vouchers.voucher_number')
                ->where('subsidiaries.store_name', $storeName)
                ->where('subsidiaries.account_code', $accountCode);

            if ($nomorInvoice) {
                $query->where('invoices.invoice', $nomorInvoice);
            }

            if ($bulan) {
                $query->whereMonth('invoices.created_at', $bulan);
            }

            if ($tahun) {
                $query->whereYear('invoices.created_at', $tahun);
            }

            $invoices = $query->get();

            $transactions = $invoices->map(function ($invoice) {
                // Fetch related invoice_payments, ordered by payment_date
                $payments = InvoicePayment::query()
                    ->select(
                        'invoice_payments.id',
                        'invoice_payments.invoice_id',
                        'invoice_payments.amount as saldo_akhir',
                        'invoice_payments.payment_date as payment_date',
                        'invoice_payments.voucher_id as payment_voucher_id',
                        'vouchers.voucher_number as nomor_voucher_pelunasan'
                    )
                    ->leftJoin('vouchers', 'invoice_payments.voucher_id', '=', 'vouchers.id')
                    ->where('invoice_payments.invoice_id', $invoice->invoice_id)
                    ->orderBy('invoice_payments.payment_date')
                    ->get();

                // Invoice row
                $invoiceRow = [
                    'type' => 'invoice',
                    'invoice_id' => $invoice->invoice_id,
                    'invoice' => $invoice->invoice,
                    'nomor_voucher_pembelian' => $invoice->nomor_voucher_pembelian ?: '-',
                    'nomor_voucher_pelunasan' => '-',
                    'voucher_date' => $invoice->tanggal,
                    'saldo_awal' => $invoice->saldo_awal,
                    'saldo_akhir' => '-',
                    'sisa_saldo' => $invoice->sisa_saldo,
                    'voucher_id' => $invoice->voucher_id ?: null
                ];

                // Payment rows with dynamic saldo_awal and sisa_saldo
                $previousSisaSaldo = $invoice->saldo_awal; // Start with invoice's total_amount
                $paymentRows = $payments->map(function ($payment) use (&$previousSisaSaldo) {
                    // Saldo Awal for this payment is the Sisa Saldo from the previous row
                    $saldoAwal = $previousSisaSaldo;
                    // Sisa Saldo is Saldo Awal - Saldo Akhir for this payment
                    $sisaSaldo = $saldoAwal - $payment->saldo_akhir;
                    // Update previousSisaSaldo for the next iteration
                    $previousSisaSaldo = $sisaSaldo;

                    return [
                        'type' => 'payment',
                        'invoice_id' => $payment->invoice_id,
                        'invoice' => null,
                        'nomor_voucher_pembelian' => '-',
                        'nomor_voucher_pelunasan' => $payment->nomor_voucher_pelunasan ?: '-',
                        'voucher_date' => $payment->payment_date,
                        'saldo_awal' => $saldoAwal,
                        'saldo_akhir' => $payment->saldo_akhir,
                        'sisa_saldo' => $sisaSaldo,
                        'payment_voucher_id' => $payment->payment_voucher_id ?: null
                    ];
                })->toArray();

                return array_merge([$invoiceRow], $paymentRows);
            })->flatten(1);

            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error in subsidiaryPiutangDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }
    public function generateSubsidiaryCode(string $accountCode): string
    {
        $latestSubsidiary = Subsidiary::where('account_code', $accountCode)
            ->orderByDesc('subsidiary_code')
            ->first();

        if (!$latestSubsidiary) {
            return $accountCode . '.01';
        }

        $lastCode = $latestSubsidiary->subsidiary_code;
        $parts = explode('.', $lastCode);
        $lastNumber = (int) end($parts);
        $newLastNumber = $lastNumber + 1;
        $parts[count($parts) - 1] = str_pad($newLastNumber, 2, '0', STR_PAD_LEFT); // Format dengan leading zero jika perlu
        return implode('.', $parts);

        // Handle kasus lain jika ada
        return $accountCode;
    }
    public function create_store(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'account_name' => 'required|string|in:Piutang Usaha,Utang Usaha',
                'store_name' => 'required|string|max:255',
            ]);

            // Mapping account names to their respective codes
            $accountCodesMap = [
                'Piutang Usaha' => '1.1.03.01',
                'Utang Usaha' => '2.1.01.01',
            ];

            // Get the account code based on the selected account name
            $accountCode = $accountCodesMap[$validatedData['account_name']];

            // Generate the subsidiary code (assumes generateSubsidiaryCode method exists)
            $generateCodeSubsidiary = $this->generateSubsidiaryCode($accountCode);

            // Check if a subsidiary with the same store_name and account_code already exists
            $existingSubsidiary = Subsidiary::where('store_name', $validatedData['store_name'])
                ->where('account_code', $accountCode)
                ->first();

            if ($existingSubsidiary) {
                return redirect()->back()->with('error', 'Nama toko sudah terdaftar untuk akun ' . $validatedData['account_name']);
            }

            // Generate the full account name for the subsidiary (e.g., "Piutang [Store Name]" or "Utang [Store Name]")
            $fullAccountName = ($validatedData['account_name'] === 'Piutang Usaha')
                ? "Piutang {$validatedData['store_name']}"
                : "Utang {$validatedData['store_name']}";

            // Create and save the new subsidiary
            $subsidiary = new Subsidiary();
            $subsidiary->subsidiary_code = $generateCodeSubsidiary;
            $subsidiary->account_name = $fullAccountName; // Store the full name (e.g., "Piutang Toko ABC")
            $subsidiary->account_code = $accountCode;
            $subsidiary->store_name = $validatedData['store_name'];
            $subsidiary->save();

            return redirect()->back()->with('success', 'Buku Besar Pembantu berhasil disimpan untuk akun ' . $validatedData['account_name']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors (though Laravel handles this automatically, good to be explicit)
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error creating subsidiary: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }
    public function generateUtangPdf(Request $request)
    {
        try {
            $invoiceNumber = $request->query('tipe_voucher_pdf');
            $storeName = $request->query('toko_pdf');
            $accountCode = $request->query('account_code_pdf');
            $month = $request->query('month_pdf');
            $year = $request->query('year_pdf');

            if (!$storeName || !$accountCode) {
                return response()->json([
                    'error' => 'Parameter toko atau kode akun diperlukan',
                    'details' => [
                        'store_name' => $storeName ? 'Provided' : 'Missing',
                        'account_code' => $accountCode ? 'Provided' : 'Missing'
                    ]
                ], 400);
            }

            $query = Invoice::query()
                ->select(
                    'invoices.id as invoice_id',
                    'invoices.invoice',
                    'invoices.created_at as tanggal',
                    'invoices.total_amount as saldo_awal',
                    'invoices.total_amount as sisa_saldo',
                    'invoices.voucher_number as nomor_voucher_pembelian',
                    'invoices.remaining_amount',
                    'vouchers.id as voucher_id'
                )
                ->join('subsidiaries', 'invoices.subsidiary_code', '=', 'subsidiaries.subsidiary_code')
                ->leftJoin('vouchers', 'invoices.voucher_number', '=', 'vouchers.voucher_number')
                ->where('subsidiaries.store_name', $storeName)
                ->where('subsidiaries.account_code', $accountCode);

            if ($invoiceNumber) {
                $query->where('invoices.invoice', $invoiceNumber);
            }

            if ($month) {
                $query->whereMonth('invoices.created_at', $month);
            }

            if ($year) {
                $query->whereYear('invoices.created_at', $year);
            }

            $invoices = $query->get();

            if ($invoices->isEmpty()) {
                return response()->json(['error' => 'Tidak ada transaksi ditemukan'], 404);
            }

            $transactions = $invoices->map(function ($invoice) {
                $payments = InvoicePayment::query()
                    ->select(
                        'invoice_payments.id',
                        'invoice_payments.invoice_id',
                        'invoice_payments.amount as saldo_akhir',
                        'invoice_payments.payment_date as payment_date',
                        'invoice_payments.voucher_id as payment_voucher_id',
                        'vouchers.voucher_number as nomor_voucher_pelunasan'
                    )
                    ->leftJoin('vouchers', 'invoice_payments.voucher_id', '=', 'vouchers.id')
                    ->where('invoice_payments.invoice_id', $invoice->invoice_id)
                    ->orderBy('invoice_payments.payment_date')
                    ->get();

                $invoiceRow = (object) [
                    'type' => 'invoice',
                    'invoice_id' => $invoice->invoice_id,
                    'invoice' => $invoice->invoice,
                    'nomor_voucher_pembelian' => $invoice->nomor_voucher_pembelian ?: '-',
                    'nomor_voucher_pelunasan' => '-',
                    'voucher_date' => $invoice->tanggal,
                    'saldo_awal' => $invoice->saldo_awal,
                    'saldo_akhir' => '-',
                    'sisa_saldo' => $invoice->sisa_saldo,
                    'voucher_id' => $invoice->voucher_id ?: null
                ];

                $previousSisaSaldo = $invoice->saldo_awal;
                $paymentRows = $payments->map(function ($payment) use (&$previousSisaSaldo) {
                    $saldoAwal = $previousSisaSaldo;
                    $sisaSaldo = $saldoAwal - $payment->saldo_akhir;
                    $previousSisaSaldo = $sisaSaldo;

                    return (object) [
                        'type' => 'payment',
                        'invoice_id' => $payment->invoice_id,
                        'invoice' => null,
                        'nomor_voucher_pembelian' => '-',
                        'nomor_voucher_pelunasan' => $payment->nomor_voucher_pelunasan ?: '-',
                        'voucher_date' => $payment->payment_date,
                        'saldo_awal' => $saldoAwal,
                        'saldo_akhir' => $payment->saldo_akhir,
                        'sisa_saldo' => $sisaSaldo,
                        'payment_voucher_id' => $payment->payment_voucher_id ?: null
                    ];
                })->toArray();

                return array_merge([$invoiceRow], $paymentRows);
            })->flatten(1);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('subsidiary_utang.subsidiary_utang_pdf', [
                'invoice_number' => $invoiceNumber ?: 'Semua Invoice',
                'transactions' => $transactions,
                'storeName' => $storeName,
                'is_all_invoices' => !$invoiceNumber // Flag for template
            ]);

            $filename = $invoiceNumber
                ? 'laporan_utang_usaha_invoice_' . $invoiceNumber . '.pdf'
                : 'laporan_utang_usaha_semua_invoice.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generating Utang PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
    public function generatePiutangPdf(Request $request)
    {
        try {
            $invoiceNumber = $request->query('tipe_voucher_pdf');
            $storeName = $request->query('toko_pdf');
            $accountCode = $request->query('account_code_pdf');
            $month = $request->query('month_pdf');
            $year = $request->query('year_pdf');

            if (!$storeName || !$accountCode) {
                return response()->json([
                    'error' => 'Parameter toko atau kode akun diperlukan',
                    'details' => [
                        'store_name' => $storeName ? 'Provided' : 'Missing',
                        'account_code' => $accountCode ? 'Provided' : 'Missing'
                    ]
                ], 400);
            }

            $query = Invoice::query()
                ->select(
                    'invoices.id as invoice_id',
                    'invoices.invoice',
                    'invoices.created_at as tanggal',
                    'invoices.total_amount as saldo_awal',
                    'invoices.total_amount as sisa_saldo',
                    'invoices.voucher_number as nomor_voucher_pembelian',
                    'invoices.remaining_amount',
                    'vouchers.id as voucher_id'
                )
                ->join('subsidiaries', 'invoices.subsidiary_code', '=', 'subsidiaries.subsidiary_code')
                ->leftJoin('vouchers', 'invoices.voucher_number', '=', 'vouchers.voucher_number')
                ->where('subsidiaries.store_name', $storeName)
                ->where('subsidiaries.account_code', $accountCode);

            if ($invoiceNumber) {
                $query->where('invoices.invoice', $invoiceNumber);
            }

            if ($month) {
                $query->whereMonth('invoices.created_at', $month);
            }

            if ($year) {
                $query->whereYear('invoices.created_at', $year);
            }

            $invoices = $query->get();

            if ($invoices->isEmpty()) {
                return response()->json(['error' => 'Tidak ada transaksi ditemukan'], 404);
            }

            $transactions = $invoices->map(function ($invoice) {
                $payments = InvoicePayment::query()
                    ->select(
                        'invoice_payments.id',
                        'invoice_payments.invoice_id',
                        'invoice_payments.amount as saldo_akhir',
                        'invoice_payments.payment_date as payment_date',
                        'invoice_payments.voucher_id as payment_voucher_id',
                        'vouchers.voucher_number as nomor_voucher_pelunasan'
                    )
                    ->leftJoin('vouchers', 'invoice_payments.voucher_id', '=', 'vouchers.id')
                    ->where('invoice_payments.invoice_id', $invoice->invoice_id)
                    ->orderBy('invoice_payments.payment_date')
                    ->get();

                $invoiceRow = (object) [
                    'type' => 'invoice',
                    'invoice_id' => $invoice->invoice_id,
                    'invoice' => $invoice->invoice,
                    'nomor_voucher_pembelian' => $invoice->nomor_voucher_pembelian ?: '-',
                    'nomor_voucher_pelunasan' => '-',
                    'voucher_date' => $invoice->tanggal,
                    'saldo_awal' => $invoice->saldo_awal,
                    'saldo_akhir' => '-',
                    'sisa_saldo' => $invoice->sisa_saldo,
                    'voucher_id' => $invoice->voucher_id ?: null
                ];

                $previousSisaSaldo = $invoice->saldo_awal;
                $paymentRows = $payments->map(function ($payment) use (&$previousSisaSaldo) {
                    $saldoAwal = $previousSisaSaldo;
                    $sisaSaldo = $saldoAwal - $payment->saldo_akhir;
                    $previousSisaSaldo = $sisaSaldo;

                    return (object) [
                        'type' => 'payment',
                        'invoice_id' => $payment->invoice_id,
                        'invoice' => null,
                        'nomor_voucher_pembelian' => '-',
                        'nomor_voucher_pelunasan' => $payment->nomor_voucher_pelunasan ?: '-',
                        'voucher_date' => $payment->payment_date,
                        'saldo_awal' => $saldoAwal,
                        'saldo_akhir' => $payment->saldo_akhir,
                        'sisa_saldo' => $sisaSaldo,
                        'payment_voucher_id' => $payment->payment_voucher_id ?: null
                    ];
                })->toArray();

                return array_merge([$invoiceRow], $paymentRows);
            })->flatten(1);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('subsidiary_utang.subsidiary_piutang_pdf', [
                'invoice_number' => $invoiceNumber ?: 'Semua Invoice',
                'transactions' => $transactions,
                'storeName' => $storeName,
                'is_all_invoices' => !$invoiceNumber // Flag for template
            ]);

            $filename = $invoiceNumber
                ? 'laporan_utang_usaha_invoice_' . $invoiceNumber . '.pdf'
                : 'laporan_utang_usaha_semua_invoice.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generating Utang PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
    public function subsidiary_excel(Request $request)
    {

        return \Maatwebsite\Excel\Facades\Excel::download(new SubsidiariesExport($request), 'laporan_subsidiari.xlsx');
    }
    public function piutang_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'store_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $subsidiary = DB::table('subsidiaries')
                ->where('id', $request->subsidiary_id)
                ->first();

            if (!$subsidiary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun pembantu piutang tidak ditemukan.',
                ], 404);
            }

            DB::table('subsidiaries')
                ->where('id', $request->subsidiary_id)
                ->update([
                    'store_name' => $request->store_name,
                    'account_name' => $request->account_name,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun pembantu piutang berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun pembantu: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function utang_update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subsidiary_id' => 'required|exists:subsidiaries,id',
            'store_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $subsidiary = DB::table('subsidiaries')
                ->where('id', $request->subsidiary_id)
                ->first();

            if (!$subsidiary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun pembantu piutang tidak ditemukan.',
                ], 404);
            }

            DB::table('subsidiaries')
                ->where('id', $request->subsidiary_id)
                ->update([
                    'store_name' => $request->store_name,
                    'account_name' => $request->account_name,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun pembantu piutang berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun pembantu: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function subsidiary_delete($id)
    {
        try {
            // Cari subsidiary berdasarkan ID
            $subsidiary = Subsidiary::findOrFail($id);

            // Hapus subsidiary
            $subsidiary->delete();

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Data subsidiary berhasil dihapus.');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika gagal
            return redirect()->back()->with('error', 'Gagal menghapus data subsidiary: ' . $e->getMessage());
        }
    }
}
