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

        // Fetch all transactions with voucher type PB to calculate average HPP per stock item
        $allTransactions = DB::table('transactions')
            ->select('transactions.description', 'transactions.nominal')
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->get();

        // Calculate average HPP for each stock item based on PB transactions only
        $hppAverages = [];
        foreach ($allTransactions as $transaction) {
            $item = $transaction->description;
            if (!isset($hppAverages[$item])) {
                $hppAverages[$item] = ['total_nominal' => 0, 'count' => 0];
            }
            $hppAverages[$item]['total_nominal'] += $transaction->nominal ?? 0;
            $hppAverages[$item]['count'] += 1;
        }

        // Compute the average HPP
        foreach ($hppAverages as $item => $data) {
            $hppAverages[$item]['average_hpp'] = $data['count'] > 0 ? $data['total_nominal'] / $data['count'] : 0;
        }

        // Fetch the earliest transaction for each stock item to determine opening balance (quantity and HPP)
        $openingBalances = DB::table('transactions')
            ->select('t1.description as item', 't1.quantity as opening_qty', 't1.nominal as opening_hpp', 't1.created_at')
            ->from('transactions as t1')
            ->join('vouchers', 't1.voucher_id', '=', 'vouchers.id')
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
            ->get()
            ->keyBy('item');

        // Fetch stock data with transactions and voucher types for the specified date
        $stockData = DB::table('stocks')
            ->select('stocks.id', 'stocks.item', 'stocks.unit', 'stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'transactions.nominal', 'vouchers.voucher_type')
            ->distinct('stocks.item')
            ->leftJoin('transactions', 'stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->get();

        // Group and calculate incoming, outgoing, opening, and final stock for each item
        $stockMap = [];
        foreach ($stockData as $stock) {
            $stockKey = $stock->item;
            if (!isset($stockMap[$stockKey])) {
                $openingBalance = $openingBalances[$stockKey] ?? null;
                $stockMap[$stockKey] = (object) [
                    'id' => $stock->id,
                    'item' => $stock->item,
                    'unit' => $stock->unit,
                    'quantity' => $stock->quantity,
                    'opening_qty' => $openingBalance ? $openingBalance->opening_qty : 0,
                    'opening_hpp' => $openingBalance ? ($openingBalance->opening_hpp ?? 0) : 0, // Nominal from earliest transaction
                    'incoming_qty' => 0,
                    'outgoing_qty' => 0,
                    'final_stock_qty' => 0,
                    'average_hpp' => $hppAverages[$stock->item]['average_hpp'] ?? 0, // PB-specific average HPP
                    'transactions' => collect()
                ];
            }

            // Incoming and outgoing stock on the filter date, excluding the earliest transaction
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->isSameDay($filterDate)) {
                $openingBalance = $openingBalances[$stockKey] ?? null;
                $isEarliestTransaction = $openingBalance && Carbon::parse($stock->created_at)->eq(Carbon::parse($openingBalance->created_at));

                if ($stock->voucher_type === 'PB' && !$isEarliestTransaction) {
                    $stockMap[$stockKey]->incoming_qty += $stock->transaction_quantity;
                } elseif ($stock->voucher_type === 'PJ') {
                    $stockMap[$stockKey]->outgoing_qty += $stock->transaction_quantity;
                }
            }

            // Collect transactions for modal (last 7 days)
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->gte(Carbon::now()->subDays(7))) {
                $stockMap[$stockKey]->transactions->push((object) [
                    'description' => $stock->description,
                    'voucher_type' => $stock->voucher_type,
                    'quantity' => $stock->transaction_quantity,
                    'nominal' => $stock->nominal,
                    'created_at' => $stock->created_at
                ]);
            }
        }

        // Calculate final stock
        foreach ($stockMap as $stock) {
            $stock->final_stock_qty = ($stock->opening_qty ?? 0) + ($stock->incoming_qty ?? 0) - ($stock->outgoing_qty ?? 0);
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
            ->where('description', 'NOT LIKE', 'HPP %')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.created_at', '>=', $dateRange)
            ->select('transactions.description', 'vouchers.voucher_type', 'transactions.quantity', 'transactions.nominal', 'transactions.created_at')
            ->get()
            ->map(function ($transaction) {
                $transaction->created_at = Carbon::parse($transaction->created_at)->format('d-m-Y');
                return $transaction;
            });

        return response()->json(['transactions' => $transactions]);
    }
}
