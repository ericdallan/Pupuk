<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\DB;

class SubsidiariesExport implements WithMultipleSheets
{
    use Exportable;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $tokoFilter = $this->request->toko_excel;
        $bulanFilter = $this->request->month_excel;
        $tahunFilter = $this->request->year_excel;
        $nomorInvoiceFilter = $this->request->tipe_voucher_excel;
        $exportType = $this->request->type;

        if ($exportType === 'piutang') {
            $piutangTransactions = $this->getPiutangTransactions($tokoFilter, $bulanFilter, $tahunFilter, $nomorInvoiceFilter);
            $sheets[] = new SubsidiaryTransactionSheet($piutangTransactions, 'Piutang Usaha');
        } elseif ($exportType === 'utang') {
            $utangTransactions = $this->getUtangTransactions($tokoFilter, $bulanFilter, $tahunFilter, $nomorInvoiceFilter);
            $sheets[] = new SubsidiaryTransactionSheet($utangTransactions, 'Utang Usaha');
        }
        // Tidak perlu else jika hanya ingin satu sheet berdasarkan halaman

        return $sheets;
    }

    private function getPiutangTransactions($toko = null, $bulan = null, $tahun = null, $nomorInvoice = null)
    {
        return Invoice::query()
            ->select(
                'invoices.id as invoice_id',
                'invoices.invoice',
                'invoices.created_at as tanggal',
                'invoices.total_amount as saldo_awal',
                DB::raw('invoices.total_amount - COALESCE(SUM(invoice_payments.amount), 0) as sisa_saldo'),
                'invoices.voucher_number as nomor_voucher_pembelian',
                'vouchers.voucher_number as nomor_voucher_pembelian_full',
                'subsidiaries.store_name',
                'subsidiaries.account_code',
            )
            ->join('subsidiaries', 'invoices.subsidiary_code', '=', 'subsidiaries.subsidiary_code')
            ->leftJoin('invoice_payments', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->leftJoin('vouchers', 'invoices.voucher_number', '=', 'vouchers.voucher_number')
            ->where('subsidiaries.account_code', '1.1.03.01')
            ->when($toko, function ($query) use ($toko) {
                return $query->where('subsidiaries.store_name', $toko);
            })
            ->when($bulan, function ($query) use ($bulan) {
                return $query->whereMonth('invoices.created_at', $bulan);
            })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('invoices.created_at', $tahun);
            })
            ->when($nomorInvoice, function ($query) use ($nomorInvoice) {
                return $query->where('invoices.invoice', $nomorInvoice);
            })
            ->groupBy('invoices.id', 'invoices.invoice', 'invoices.created_at', 'invoices.total_amount', 'invoices.voucher_number', 'vouchers.voucher_number', 'subsidiaries.store_name', 'subsidiaries.account_code')
            ->orderBy('invoices.created_at')
            ->get();
    }

    private function getUtangTransactions($toko = null, $bulan = null, $tahun = null, $nomorInvoice = null)
    {
        return Invoice::query()
            ->select(
                'invoices.id as invoice_id',
                'invoices.invoice',
                'invoices.created_at as tanggal',
                'invoices.total_amount as saldo_awal',
                DB::raw('invoices.total_amount - COALESCE(SUM(invoice_payments.amount), 0) as sisa_saldo'),
                'invoices.voucher_number as nomor_voucher_pembelian',
                'vouchers.voucher_number as nomor_voucher_pembelian_full',
                'subsidiaries.store_name',
                'subsidiaries.account_code',
            )
            ->join('subsidiaries', 'invoices.subsidiary_code', '=', 'subsidiaries.subsidiary_code')
            ->leftJoin('invoice_payments', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->leftJoin('vouchers', 'invoices.voucher_number', '=', 'vouchers.voucher_number')
            ->where('subsidiaries.account_code', '2.1.01.01')
            ->when($toko, function ($query) use ($toko) {
                return $query->where('subsidiaries.store_name', $toko);
            })
            ->when($bulan, function ($query) use ($bulan) {
                return $query->whereMonth('invoices.created_at', $bulan);
            })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('invoices.created_at', $tahun);
            })
            ->when($nomorInvoice, function ($query) use ($nomorInvoice) {
                return $query->where('invoices.invoice', $nomorInvoice);
            })
            ->groupBy('invoices.id', 'invoices.invoice', 'invoices.created_at', 'invoices.total_amount', 'invoices.voucher_number', 'vouchers.voucher_number', 'subsidiaries.store_name', 'subsidiaries.account_code')
            ->orderBy('invoices.created_at')
            ->get();
    }
}
