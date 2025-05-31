<?php

namespace App\Services;

use App\Models\Subsidiary;
use App\Models\Voucher;
use App\Models\VoucherDetails;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SubsidiaryService
{
    /**
     * Generate a new subsidiary code based on the given account code
     *
     * @param string $accountCode
     * @return string
     */
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
        $parts[count($parts) - 1] = str_pad($newLastNumber, 2, '0', STR_PAD_LEFT);
        return implode('.', $parts);
    }

    /**
     * Prepare data for the Subsidiary Utang page
     *
     * @param array $filters
     * @return array
     */
    public function prepareUtangData(array $filters): array
    {
        $query = Subsidiary::query();

        if (!empty($filters['toko'])) {
            $query->where('store_name', $filters['toko']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        $subsidiaries = $query->get();

        $voucherQuery = Voucher::query();

        if (!empty($filters['month'])) {
            $voucherQuery->whereMonth('voucher_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $voucherQuery->whereYear('voucher_date', $filters['year']);
        }

        $vouchers = $voucherQuery->get();

        $voucherDetailsQuery = VoucherDetails::query()
            ->selectRaw('vouchers.store, voucher_details.account_code, SUM(voucher_details.debit - voucher_details.credit) as total_saldo')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->groupBy('vouchers.store', 'voucher_details.account_code');

        if (!empty($filters['month'])) {
            $voucherDetailsQuery->whereMonth('vouchers.voucher_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $voucherDetailsQuery->whereYear('vouchers.voucher_date', $filters['year']);
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

        return [
            'subsidiaries' => $subsidiaries,
            'utangUsaha' => $utangUsaha,
        ];
    }

    /**
     * Prepare data for the Subsidiary Piutang page
     *
     * @param array $filters
     * @return array
     */
    public function preparePiutangData(array $filters): array
    {
        $query = Subsidiary::query();

        if (!empty($filters['toko'])) {
            $query->where('store_name', $filters['toko']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        $subsidiaries = $query->with('invoices')->get();

        $voucherQuery = Voucher::query();

        if (!empty($filters['month'])) {
            $voucherQuery->whereMonth('voucher_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $voucherQuery->whereYear('voucher_date', $filters['year']);
        }

        $vouchers = $voucherQuery->get();

        $voucherDetailsQuery = VoucherDetails::query()
            ->selectRaw('vouchers.store, voucher_details.account_code, SUM(voucher_details.debit - voucher_details.credit) as total_saldo')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->groupBy('vouchers.store', 'voucher_details.account_code');

        if (!empty($filters['month'])) {
            $voucherDetailsQuery->whereMonth('vouchers.voucher_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $voucherDetailsQuery->whereYear('vouchers.voucher_date', $filters['year']);
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

        return [
            'subsidiaries' => $subsidiaries,
            'piutangUsaha' => $piutangUsaha,
        ];
    }

    /**
     * Fetch detailed transactions for a subsidiary (Utang or Piutang)
     *
     * @param array $filters
     * @return Collection
     */
    public function getTransactions(array $filters): Collection
    {
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
            ->where('subsidiaries.store_name', $filters['store_name'])
            ->where('subsidiaries.account_code', $filters['account_code']);

        if (!empty($filters['tipe_voucher'])) {
            $query->where('invoices.invoice', $filters['tipe_voucher']);
        }

        if (!empty($filters['bulan'])) {
            $query->whereMonth('invoices.created_at', $filters['bulan']);
        }

        if (!empty($filters['tahun'])) {
            $query->whereYear('invoices.created_at', $filters['tahun']);
        }

        $invoices = $query->get();

        return $invoices->map(function ($invoice) {
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

            $previousSisaSaldo = $invoice->saldo_awal;
            $paymentRows = $payments->map(function ($payment) use (&$previousSisaSaldo) {
                $saldoAwal = $previousSisaSaldo;
                $sisaSaldo = $saldoAwal - $payment->saldo_akhir;
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
    }

    /**
     * Prepare data for PDF generation (Utang or Piutang)
     *
     * @param array $filters
     * @return array
     */
    public function preparePdfData(array $filters): array
    {
        $storeName = $filters['toko_pdf'] ?? null;
        $accountCode = $filters['account_code_pdf'] ?? null;
        $invoiceNumber = $filters['tipe_voucher_pdf'] ?? null;
        $month = $filters['month_pdf'] ?? null;
        $year = $filters['year_pdf'] ?? null;

        if (!$storeName || !$accountCode) {
            throw new \Exception('Parameter toko atau kode akun diperlukan');
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

        return [
            'invoice_number' => $invoiceNumber ?: 'Semua Invoice',
            'transactions' => $transactions,
            'storeName' => $storeName,
            'is_all_invoices' => !$invoiceNumber,
        ];
    }

    /**
     * Update subsidiary data
     *
     * @param array $data
     * @return bool
     */
    public function updateSubsidiary(array $data): bool
    {
        DB::beginTransaction();

        try {
            $subsidiary = DB::table('subsidiaries')
                ->where('id', $data['subsidiary_id'])
                ->first();

            if (!$subsidiary) {
                throw new \Exception('Akun pembantu piutang tidak ditemukan.');
            }

            DB::table('subsidiaries')
                ->where('id', $data['subsidiary_id'])
                ->update([
                    'store_name' => $data['store_name'],
                    'account_name' => $data['account_name'],
                    'updated_at' => now(),
                ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a subsidiary
     *
     * @param int $id
     * @return bool
     */
    public function deleteSubsidiary(int $id): bool
    {
        $subsidiary = Subsidiary::find($id);
        if ($subsidiary) {
            $subsidiary->delete();
            return true;
        }
        throw new \Exception('Subsidiary not found.');
    }
}
