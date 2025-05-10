<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\StockExport;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    public function stock_page(Request $request)
    {
        // Get the filter date from request, default to today
        $filterDate = $request->input('filter_date', Carbon::today()->toDateString());
        $filterDate = Carbon::parse($filterDate)->startOfDay();

        // Fetch stock data with transactions and voucher types
        $stockData = DB::table('stocks')
            ->select('stocks.id', 'stocks.item', 'stocks.unit', 'stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'vouchers.voucher_type')
            ->distinct('stocks.item')
            ->leftJoin('transactions', 'stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->get();

        // Group and calculate incoming, outgoing, and final stock for each item
        $stockMap = [];
        foreach ($stockData as $stock) {
            $stockKey = $stock->item;
            if (!isset($stockMap[$stockKey])) {
                $stockMap[$stockKey] = (object) [
                    'id' => $stock->id,
                    'item' => $stock->item,
                    'unit' => $stock->unit,
                    'quantity' => $stock->quantity,
                    'incoming' => 0,
                    'outgoing' => 0,
                    'final_stock' => 0,
                    'transactions' => collect()
                ];
            }

            // Incoming and outgoing stock on the filter date
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->isSameDay($filterDate)) {
                if ($stock->voucher_type === 'PB') {
                    $stockMap[$stockKey]->incoming += $stock->transaction_quantity;
                } elseif ($stock->voucher_type === 'PJ') {
                    $stockMap[$stockKey]->outgoing += $stock->transaction_quantity;
                }
            }

            // Collect transactions for modal (last 7 days)
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->gte(Carbon::now()->subDays(7))) {
                $stockMap[$stockKey]->transactions->push((object) [
                    'description' => $stock->description,
                    'voucher_type' => $stock->voucher_type,
                    'quantity' => $stock->transaction_quantity,
                    'created_at' => $stock->created_at
                ]);
            }
        }

        // Calculate final stock
        foreach ($stockMap as $stock) {
            $stock->final_stock = ($stock->incoming ?? 0) - ($stock->outgoing ?? 0);
        }

        return view('stock.stock_page', ['stockData' => array_values($stockMap)]);
    }

    public function export(Request $request)
    {
        $filterDate = $request->input('filter_date', Carbon::today()->toDateString());
        return Excel::download(new StockExport($filterDate), 'stock_report_' . $filterDate . '.xlsx');
    }

    public function get_transactions($stockId, Request $request)
    {
        $filter = $request->input('filter', '7_days');
        $dateRange = $filter === '7_days' ? Carbon::now()->subDays(7) : Carbon::now()->subMonth();

        $stock = DB::table('stocks')->where('id', $stockId)->first();
        if (!$stock) {
            return response()->json(['transactions' => []]);
        }

        $transactions = DB::table('transactions')
            ->where('description', $stock->item)
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.created_at', '>=', $dateRange)
            ->select('transactions.description', 'vouchers.voucher_type', 'transactions.quantity', 'transactions.created_at')
            ->get()
            ->map(function ($transaction) {
                $transaction->created_at = Carbon::parse($transaction->created_at)->format('d-m-Y');
                return $transaction;
            });

        return response()->json(['transactions' => $transactions]);
    }
}