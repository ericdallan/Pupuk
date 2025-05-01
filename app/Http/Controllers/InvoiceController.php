<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function deleteWithPayments(Invoice $invoice)
    {
        // Hapus semua invoice_payments yang terkait dengan invoice
        $invoice->payment_vouchers()->delete();

        // Setelah payments dihapus, hapus invoice itu sendiri
        $invoice->delete();

        return redirect()->back()->with('success', 'Invoice beserta semua pembayarannya berhasil dihapus.');
    }

    // Method destroy untuk menghapus invoice tanpa payments (jika ada)
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus.');
    }

    // Method edit (kemungkinan sudah ada)
    public function edit(Invoice $invoice)
    {
        // ... logika untuk menampilkan form edit invoice
    }
}
