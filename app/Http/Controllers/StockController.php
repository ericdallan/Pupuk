<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function stock_page()
    {
        // Ambil data dari tabel stocks, pastikan setiap item hanya muncul sekali
        $stockData = DB::table('stocks')
            ->select('id', 'item', 'unit', 'quantity') // Pilih hanya kolom yang dibutuhkan
            ->distinct('item')
            ->get();

        // Ambil semua transaksi terkait dengan item-item yang ada di $stockData
        $transactions = DB::table('transactions')
            ->whereIn('description', $stockData->pluck('item'))
            ->get();

        // Tambahkan data transaksi ke setiap item di $stockData
        foreach ($stockData as $stock) {
            $stock->transactions = $transactions->where('description', $stock->item)->values();
        }

        return view('stock.stock_page', ['stockData' => $stockData]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stock $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        //
    }
}
