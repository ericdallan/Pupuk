<?php

namespace App\Exports;

use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class SubsidiaryTransactionSheet
 *
 * Exports subsidiary transaction data to an Excel sheet with formatted invoices and payments.
 */
class SubsidiaryTransactionSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private const TYPE_INVOICE = 'Invoice';
    private const TYPE_PEMBAYARAN = 'Pembayaran';

    private Collection $transactions;
    private string $title;

    /**
     * SubsidiaryTransactionSheet constructor.
     *
     * @param Collection $transactions The collection of transactions to export
     * @param string     $title        The title of the Excel sheet
     */
    public function __construct(Collection $transactions, string $title)
    {
        $this->transactions = $transactions;
        $this->title = $title;
    }

    /**
     * Generates the collection of data to be exported to Excel.
     *
     * @return Collection
     */
    public function collection(): Collection
    {
        $exportData = new Collection();

        foreach ($this->transactions->groupBy('invoice_id') as $invoiceId => $invoiceTransactions) {
            $invoice = $invoiceTransactions->firstWhere('invoice_id', $invoiceId);

            if ($invoice) {
                $exportData->push($this->mapInvoiceRow($invoice));

                $payments = $this->getInvoicePayments($invoiceId);
                $currentSaldo = $invoice->saldo_awal;

                foreach ($payments as $payment) {
                    $currentSaldo -= $payment->saldo_akhir;
                    $exportData->push($this->mapPaymentRow($invoice, $payment, $currentSaldo));
                }
            }
        }

        return $exportData;
    }

    /**
     * Maps an invoice object to an array for Excel export.
     *
     * @param object $invoice The invoice data
     *
     * @return array<string, string>
     */
    private function mapInvoiceRow(object $invoice): array
    {
        return [
            'Jenis' => self::TYPE_INVOICE,
            'Nomor Invoice' => $invoice->invoice ?? '-',
            'Nomor Voucher Pembelian' => $invoice->nomor_voucher_pembelian_full ?? '-',
            'Nomor Voucher Pelunasan' => '-',
            'Tanggal' => $this->formatDate($invoice->tanggal),
            'Saldo Awal' => number_format($invoice->saldo_awal, 2, ',', '.'),
            'Saldo Akhir' => '-',
            'Sisa Saldo' => number_format($invoice->sisa_saldo, 2, ',', '.'),
        ];
    }

    /**
     * Maps a payment object to an array for Excel export.
     *
     * @param object $invoice       The related invoice data
     * @param object $payment       The payment data
     * @param float  $currentSaldo  The current saldo after payment
     *
     * @return array<string, string>
     */
    private function mapPaymentRow(object $invoice, object $payment, float $currentSaldo): array
    {
        return [
            'Jenis' => self::TYPE_PEMBAYARAN,
            'Nomor Invoice' => $invoice->invoice ?? '-',
            'Nomor Voucher Pembelian' => '-',
            'Nomor Voucher Pelunasan' => $payment->nomor_voucher_pelunasan ?? '-',
            'Tanggal' => $this->formatDate($payment->payment_date),
            'Saldo Awal' => '-',
            'Saldo Akhir' => number_format($payment->saldo_akhir, 2, ',', '.'),
            'Sisa Saldo' => number_format($currentSaldo, 2, ',', '.'),
        ];
    }

    /**
     * Retrieves payment data for a specific invoice.
     *
     * @param int $invoiceId The ID of the invoice
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getInvoicePayments(int $invoiceId): \Illuminate\Database\Eloquent\Collection
    {
        return InvoicePayment::query()
            ->select([
                'invoice_payments.amount as saldo_akhir',
                'invoice_payments.payment_date',
                'vouchers.voucher_number as nomor_voucher_pelunasan',
            ])
            ->leftJoin('vouchers', 'invoice_payments.voucher_id', '=', 'vouchers.id')
            ->where('invoice_id', $invoiceId)
            ->orderBy('invoice_payments.payment_date')
            ->get();
    }

    /**
     * Formats a date string to a readable format (e.g., "01 January 2023").
     *
     * @param string|null $date The date string to format
     *
     * @return string The formatted date or '-' if null
     */
    private function formatDate(?string $date): string
    {
        return $date ? Carbon::parse($date)->format('d F Y') : '-';
    }

    /**
     * Defines the column headings for the Excel sheet.
     *
     * @return array<string>
     */
    public function headings(): array
    {
        return [
            'Jenis',
            'Nomor Invoice',
            'Nomor Voucher Pembelian',
            'Nomor Voucher Pelunasan',
            'Tanggal',
            'Saldo Awal',
            'Saldo Akhir',
            'Sisa Saldo',
        ];
    }

    /**
     * Returns the title of the Excel sheet.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Applies styles to the Excel sheet, including background color and borders for the header row.
     *
     * @param Worksheet $sheet The worksheet to style
     *
     * @return void
     */
    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'], // White font color
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4BACC6'], // Light blue background
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'], // Black border
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Optional: Auto-size columns for better readability
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
