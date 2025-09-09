<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;
use Carbon\Carbon;
use App\Exports\StockExport;
use Illuminate\Support\Facades\DB;
use App\Exports\StockImportTemplate;
use App\Imports\StockImport;
use App\Models\Recipes;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display the Stock page
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function stock_page(Request $request)
    {
        try {
            $data = $this->stockService->prepareStockData($request->all());
            return view('stock.stock_page', $data);
        } catch (\Exception $e) {
            Log::error('Stock Page Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal memuat halaman stok']);
        }
    }

    /**
     * Export stock data to Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::today()->startOfYear()->toDateString());
            $endDate = $request->input('end_date', Carbon::today()->toDateString());
            $data = $this->stockService->prepareExportData($startDate, $endDate);
            return Excel::download(
                new StockExport($data['startDate'], $data['endDate'], $data),
                'stock_report_' . $data['startDate']->toDateString() . '_to_' . $data['endDate']->toDateString() . '.xlsx'
            );
        } catch (\Exception $e) {
            Log::error('Stock Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal mengekspor data stok']);
        }
    }

    /**
     * Generate and download a PDF transfer form
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function printTransferForm(Request $request)
    {
        try {
            $tableFilter = $request->input('table_filter', 'all');
            $data = $this->stockService->prepareTransferFormData($tableFilter);
            $pdf = Pdf::loadView('stock.stock_pdf', $data);
            return $pdf->download('Formulir_Pemindahan_Barang_' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Print Transfer Form Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal menghasilkan formulir pemindahan']);
        }
    }

    /**
     * Store a new recipe
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRecipe(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s]+$/',
                'product_size' => 'required|string|max:255',
                'transfer_stock_id.*' => 'required|exists:transfer_stocks,id',
                'quantity.*' => 'required|integer|min:1',
                'nominal.*' => 'required|numeric|min:0',
            ]);

            // Pengecekan duplikasi product_name dan product_size (case-insensitive)
            $existingRecipe = Recipes::whereRaw('LOWER(product_name) = ?', [strtolower($validated['product_name'])])
                ->whereRaw('LOWER(size) = ?', [strtolower($validated['product_size'])])
                ->first();

            if ($existingRecipe) {
                return redirect()->back()->withErrors([
                    'product_name' => 'Kombinasi nama produk dan ukuran sudah ada. Produk "' . $existingRecipe->product_name . '" dengan ukuran "' . $existingRecipe->size . '" sudah terdaftar.'
                ])->withInput();
            }

            Log::debug('Store Recipe Input:', [
                'product_name' => $validated['product_name'],
                'product_size' => $validated['product_size'],
                'transfer_stock_ids' => $request->input('transfer_stock_id'),
                'quantities' => $request->input('quantity'),
                'nominals' => $request->input('nominal'),
            ]);

            $this->stockService->storeRecipe(
                $validated['product_name'],
                $request->input('transfer_stock_id'),
                $request->input('quantity'),
                $validated['product_size']
            );

            return redirect()->back()->with('success', 'Resep berhasil disimpan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Store Recipe Validation Error: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Store Recipe Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan resep: ' . $e->getMessage()]);
        }
    }
    /**
     * Get recipe ingredients for editing
     *
     * @param int $recipeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecipeIngredients($recipeId)
    {
        try {
            $recipe = Recipes::with(['transferStocks'])->findOrFail($recipeId);

            // Check if recipe has been used in transactions
            $hasTransactions = DB::table('transactions')
                ->where('description', $recipe->product_name)
                ->where('size', $recipe->size)
                ->exists();

            if ($hasTransactions) {
                return response()->json([
                    'error' => 'Recipe sudah digunakan dalam transaksi dan tidak dapat diedit'
                ], 400);
            }

            $ingredients = $recipe->transferStocks->map(function ($transferStock) {
                return [
                    'transfer_stock_id' => $transferStock->id,
                    'item' => $transferStock->item,
                    'size' => $transferStock->size,
                    'quantity' => $transferStock->pivot->quantity,
                    'nominal' => $transferStock->pivot->nominal,
                ];
            });

            return response()->json([
                'recipe' => [
                    'id' => $recipe->id,
                    'product_name' => $recipe->product_name,
                    'size' => $recipe->size,
                    'nominal' => $recipe->nominal,
                ],
                'ingredients' => $ingredients
            ]);
        } catch (\Exception $e) {
            Log::error('Get Recipe Ingredients Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Gagal memuat data recipe'], 500);
        }
    }

    /**
     * Update a recipe
     *
     * @param Request $request
     * @param int $recipeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRecipe(Request $request, $recipeId)
    {
        try {
            $recipe = Recipes::findOrFail($recipeId);

            // Check if recipe has been used in transactions
            $hasTransactions = DB::table('transactions')
                ->where('description', $recipe->product_name)
                ->where('size', $recipe->size)
                ->exists();

            if ($hasTransactions) {
                return redirect()->back()->withErrors([
                    'error' => 'Recipe sudah digunakan dalam transaksi dan tidak dapat diedit'
                ]);
            }

            $validated = $request->validate([
                'product_name' => 'required|string|max:255|regex:/^[A-Za-z0-9\s]+$/',
                'product_size' => 'required|string|max:255',
                'transfer_stock_id.*' => 'required|exists:transfer_stocks,id',
                'quantity.*' => 'required|integer|min:1',
                'nominal.*' => 'required|numeric|min:0',
            ]);

            // Check for duplicate recipe name and size (excluding current recipe)
            $existingRecipe = Recipes::where('id', '!=', $recipeId)
                ->whereRaw('LOWER(product_name) = ?', [strtolower($validated['product_name'])])
                ->whereRaw('LOWER(size) = ?', [strtolower($validated['product_size'])])
                ->first();

            if ($existingRecipe) {
                return redirect()->back()->withErrors([
                    'product_name' => 'Kombinasi nama produk dan ukuran sudah ada. Produk "' . $existingRecipe->product_name . '" dengan ukuran "' . $existingRecipe->size . '" sudah terdaftar.'
                ])->withInput();
            }

            $this->stockService->updateRecipe(
                $recipeId,
                $validated['product_name'],
                $request->input('transfer_stock_id'),
                $request->input('quantity'),
                $validated['product_size']
            );

            return redirect()->back()->with('success', 'Recipe berhasil diupdate.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Update Recipe Validation Error: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Update Recipe Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal mengupdate recipe: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a recipe
     *
     * @param int $recipeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteRecipe($recipeId)
    {
        try {
            $recipe = Recipes::findOrFail($recipeId);

            // Check if recipe has been used in transactions
            $hasTransactions = DB::table('transactions')
                ->where('description', $recipe->product_name)
                ->where('size', $recipe->size)
                ->exists();

            if ($hasTransactions) {
                return redirect()->back()->withErrors([
                    'error' => 'Recipe sudah digunakan dalam transaksi dan tidak dapat dihapus'
                ]);
            }

            $this->stockService->deleteRecipe($recipeId);

            return redirect()->back()->with('success', 'Recipe berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Delete Recipe Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus recipe: ' . $e->getMessage()]);
        }
    }
}
