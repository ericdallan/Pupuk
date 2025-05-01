<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccountCodeExport;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;


class AccountCodeController extends Controller
{
    public function account_page()
    {
        $accounts = ChartOfAccount::all();
        $hierarkiAkun = $this->buildHierarchy($accounts);

        return view('AccountCode.accountCode_page', ['hierarkiAkun' => $hierarkiAkun]);
    }

    private function buildHierarchy($accounts)
    {
        $hierarki = [];

        foreach ($accounts as $account) {
            $accountType = $account->account_type;
            $accountSection = $account->account_section;
            $accountSubsection = $account->account_subsection;
            $accountName = $account->account_name;
            $accountCode = $account->account_code;

            if (!isset($hierarki[$accountType])) {
                $hierarki[$accountType] = [];
            }

            if (!isset($hierarki[$accountType][$accountSection])) {
                $hierarki[$accountType][$accountSection] = [];
            }

            if ($accountSubsection) {
                if (!isset($hierarki[$accountType][$accountSection][$accountSubsection])) {
                    $hierarki[$accountType][$accountSection][$accountSubsection] = [];
                }

                if ($accountCode) {
                    $hierarki[$accountType][$accountSection][$accountSubsection][] = [$accountName, $accountCode];
                } else {
                    $hierarki[$accountType][$accountSection][$accountSubsection][] = $accountName;
                }
            } else {
                if ($accountCode) {
                    $hierarki[$accountType][$accountSection][] = [$accountName, $accountCode];
                } else {
                    $hierarki[$accountType][$accountSection][] = $accountName;
                }
            }
        }

        return $hierarki;
    }

    public function generateAccountCode(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'account_type' => 'required|string',
            'account_section' => 'required|string',
            'account_subsection' => 'required|string',
        ]);

        $accountType = $request->input('account_type');
        $accountSection = $request->input('account_section');
        $accountSubsection = $request->input('account_subsection');

        // Generate prefix untuk account code
        $prefix = $this->generatePrefix($accountType, $accountSection, $accountSubsection);

        if (!$prefix) {
            return response()->json(['error' => 'Prefix account code tidak dapat dibuat.'], 400);
        }

        // Cari kode akun terakhir dengan prefix yang sama dari database
        $lastAccount = ChartOfAccount::where('account_code', 'like', $prefix . '.%')
            ->orderBy('account_code', 'desc')
            ->first();

        $lastIncrement = 0;
        if ($lastAccount) {
            $parts = explode('.', $lastAccount->account_code);
            $lastIncrement = (int) end($parts);
        }

        // Increment nomor terakhir
        $newIncrement = $lastIncrement + 1;

        // Pastikan tidak melebihi 99
        if ($newIncrement > 99) {
            return response()->json(['error' => 'Nomor urut akun telah mencapai batas maksimum (99) untuk kombinasi ini.'], 400);
        }

        // Format nomor increment dengan leading zero jika perlu
        $formattedIncrement = str_pad($newIncrement, 2, '0', STR_PAD_LEFT);

        // Gabungkan prefix dengan nomor increment
        $newAccountCode = $prefix . '.' . $formattedIncrement;

        return response()->json(['account_code' => $newAccountCode]);
    }

    protected function generatePrefix($type, $section, $subsection)
    {

        // Contoh sederhana (Anda perlu menyesuaikannya dengan struktur data Anda):
        $typeCode = $this->getTypeCode($type);
        $sectionCode = $this->getSectionCode($section, $type);
        $subsectionCode = $this->getSubsectionCode($subsection, $type, $section);

        if ($typeCode !== null && $sectionCode !== null && $subsectionCode !== null) {
            return $typeCode . '.' . $sectionCode . '.' . $subsectionCode;
        }

        return null;
    }

    // Fungsi-fungsi pembantu untuk mendapatkan kode berdasarkan nama
    protected function getTypeCode($typeName)
    {
        // Implementasikan logika untuk mendapatkan kode tipe akun berdasarkan nama
        // Misalnya, menggunakan array atau query ke tabel referensi
        $types = [
            'ASET' => '1',
            'KEWAJIBAN' => '2',
            'EKUITAS' => '3',
            'PENDAPATAN_USAHA' => '4',
            'HARGA_POKOK_PRODUKSI_DAN_PENJUALAN' => '5',
            'BEBAN_BEBAN_USAHA' => '6',
            'PENDAPATAN_DAN_BEBAN_LAIN_LAIN' => '7',
        ];
        return $types[$typeName] ?? null;
    }

    protected function getSectionCode($sectionName, $accountType)
    {
        // Implementasikan logika untuk mendapatkan kode bagian akun berdasarkan nama
        // Ini bisa lebih kompleks tergantung bagaimana Anda menyimpan hierarki
        $sections = [
            'ASET' => [
                'Aset Lancar' => '1',
                'Investasi' => '2',
                'Aset Tetap' => '3',
            ],
            'KEWAJIBAN' => [
                'Kewajiban Jangka Pendek' => '1',
                'Kewajiban Jangka Panjang' => '2',
            ],
            'EKUITAS' => [
                'Modal Pemilik' => '1',
                'Pengambilan oleh Pemilik' => '2',
                'Saldo Laba' => '3',
                'Ikhtisar Laba Rugi' => '4',
                'Laba Rugi Ditahan' => '5',
            ],
            'PENDAPATAN_USAHA' => [
                'Pendapatan Penjualan Barang Dagangan' => '1',
                'Pendapatan Penjualan Barang Jadi' => '2',
            ],
            'HARGA_POKOK_PRODUKSI_DAN_PENJUALAN' => [
                'Harga Pokok Penjualan Barang Dagangan' => '1',
                'Harga Pokok Penjualan Barang Jadi' => '2',
                'Harga Pokok Produksi' => '3',
            ],
            'BEBAN_BEBAN_USAHA' => [
                'Beban Administrasi dan Umum' => '1',
                'Beban Operasional' => '2',
                'Beban Pemasaran' => '3',
            ],
            'PENDAPATAN_DAN_BEBAN_LAIN_LAIN' => [
                'Pendapatan Lain-lain' => '1',
                'Beban Lain-lain' => '2',
                'Beban Pajak',
            ]
        ];

        return $sections[$accountType][$sectionName] ?? null;
    }

    protected function getSubsectionCode($subsectionName, $accountType, $accountSection)
    {
        // Implementasikan logika untuk mendapatkan kode sub-bagian akun berdasarkan nama
        // Ini juga bisa lebih kompleks
        $subsections = [
            'ASET' => [
                'Aset Lancar' => [
                    'Kas dan Setara Kas' => '01',
                    'Piutang' => '03',
                    'Penyisihan Piutang' => '04',
                    'Persediaan' => '05',
                    'Pembayaran Dimuka' => '07',
                    'Aset Lancar Lainnya' => '98',
                ],
                'Investasi' => [
                    'Investasi' => '01',
                ],
                'Aset Tetap' => [
                    'Aset Tetap' => '00',
                    'Akumulasi Penyusutan Aset Tetap' => '07',
                ],
            ],
            'KEWAJIBAN' => [
                'Kewajiban Jangka Pendek' => [
                    'Utang Usaha' => '01',
                    'Utang Pajak' => '02',
                    'Utang Gaji/Upah dan Tunjangan' => '03',
                    'Utang Utilitas' => '04',
                    'Utang Jangka Pendek Lainnya' => '09',
                ],
                'Kewajiban Jangka Panjang' => [
                    'Utang Ke Bank' => '01',
                    'Utang Jangka Panjang Lainnya' => '99',
                ],
            ],
            'EKUITAS' => [
                'Modal Pemilik' => [
                    'Modal Pemilik' => '00',
                ],
                'Pengambilan oleh Pemilik' => [
                    'Pengambilan oleh Pemilik' => '00',
                ],
                'Saldo Laba' => [
                    'Saldo Laba' => '01',
                ],
                'Ikhtisar Laba Rugi' => [
                    'Ikhtisar Laba Rugi' => '01',
                ],
                'Laba Rugi Ditahan' => [
                    'Laba Rugi Ditahan' => '01',
                ],
            ],
            'PENDAPATAN_USAHA' => [
                'Pendapatan Penjualan Barang Dagangan' => [
                    'Harga Pokok Penjualan Barang Dagangan' => '01',
                    'Diskon Penjualan Barang Dagangan' => '02',
                ],
                'Pendapatan Penjualan Barang Jadi' => [
                    'Pendapatan Penjualan Barang Jadi' => '01',
                    'Diskon Penjualan Barang Dagangan' => '03',
                ],
            ],
            'HARGA_POKOK_PRODUKSI_DAN_PENJUALAN' => [
                'Harga Pokok Penjualan Barang Dagangan' => [
                    'Harga Pokok Penjualan Barang Dagangan' => '01',
                ],
                'Harga Pokok Penjualan Barang Jadi' => [
                    'Harga Pokok Penjualan Barang Jadi' => '01',
                ],
                'Harga Pokok Produksi' => [
                    'Biaya Bahan Baku' => '01',
                    'Beban Upah dan Tunjangan Bag. Produksi' => '02',
                    'Beban Lembur, Insentif (Bonus) Bag. Produksi' => '03',
                    'Biaya Overhead' => '04',
                ],
            ],
            'BEBAN_BEBAN_USAHA' => [
                'Beban Administrasi dan Umum' => [
                    'Beban Pegawai Bagian Administrasi Umum' => '01',
                    'Beban Perlengkapan' => '02',
                    'Beban Pemeliharaan dan Perbaikan Peralatan Kantor' => '03',
                    'Beban Utilitas' => '04',
                    'Beban Sewa dan Asuransi' => '05',
                    'Beban Kebersihan dan Keamanan' => '06',
                    'Beban Penyisihan dan Penyusutan/Amortisasi' => '07',
                    'Beban Administrasi dan Umum Lainnya' => '99',
                ],
                'Beban Operasional' => [
                    'Beban Pegawai Bagian Operasional' => '01',
                    'Beban Pemeliharaan dan Perbaikan' => '02',
                    'Beban Operasional Lainnya' => '99',
                ],
                'Beban Pemasaran' => [
                    'Beban Pegawai Bagian Pemasaran' => '01',
                    'Beban Pemasaran Lainnya' => '99',
                ],
            ],
            'PENDAPATAN_DAN_BEBAN_LAIN_LAIN' => [
                'Pendapatan Lain-lain' => [
                    'Pendapatan dari Bank' => '01',
                    'Pendapatan Penjualan Aset Tetap' => '02',
                    'Pendapatan Lain-lain lainnya' => '03',
                ],
                'Beban Lain-lain' => [
                    'Beban Bank' => '01',
                    'Beban Bunga' => '02',
                    'Beban Penjualan Aset Tetap' => '03',
                    'Beban Lain-lain lainnya' => '99',
                ],
                'Beban Pajak' => [
                    'Beban Pajak' => '01'
                ],
            ],
        ];

        return $subsections[$accountType][$accountSection][$subsectionName] ?? null;
    }
    public function create_account(Request $request)
    {
        // Validasi input dari form (sama seperti sebelumnya)
        $validator = Validator::make($request->all(), [
            'account_type' => 'required|string',
            'account_section' => 'required|string',
            'account_subsection' => 'required|string',
            'account_name' => 'required|string|max:255|unique:chart_of_accounts,account_name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $accountType = $request->input('account_type');
        $accountSection = $request->input('account_section');
        $accountSubsection = $request->input('account_subsection');
        $accountName = $request->input('account_name');

        // Generate prefix untuk account code
        $prefix = $this->generatePrefix($accountType, $accountSection, $accountSubsection);

        if (!$prefix) {
            return redirect()->route('account_page')
                ->with('error', 'Prefix account code tidak dapat dibuat.')
                ->withInput();
        }

        // Cari semua kode akun dengan prefix yang sama
        $existingAccounts = ChartOfAccount::where('account_code', 'like', $prefix . '.%')
            ->orderBy('account_code')
            ->get();

        $availableIncrement = null;
        $usedIncrements = [];

        foreach ($existingAccounts as $account) {
            $parts = explode('.', $account->account_code);
            if (count($parts) === 4) { // Pastikan formatnya prefix.xx
                $increment = (int) end($parts);
                $usedIncrements[] = $increment;
            }
        }

        // Cari nomor increment yang belum terpakai dari 1 hingga 99
        for ($i = 1; $i <= 99; $i++) {
            if (!in_array($i, $usedIncrements)) {
                $availableIncrement = $i;
                break;
            }
        }

        if ($availableIncrement === null) {
            return redirect()->route('account_page') // Ganti 'nama_route_form_anda'
                ->with('error', 'Nomor urut akun telah mencapai batas maksimum (99) untuk kombinasi ini.')
                ->withInput();
        }

        // Format nomor increment dengan leading zero
        $formattedIncrement = str_pad($availableIncrement, 2, '0', STR_PAD_LEFT);

        // Gabungkan prefix dengan nomor increment
        $newAccountCode = $prefix . '.' . $formattedIncrement;

        // Buat instance model ChartOfAccount baru
        $newAccount = new ChartOfAccount();
        $newAccount->account_code = $newAccountCode;
        $newAccount->account_type = $accountType;
        $newAccount->account_section = $accountSection;
        $newAccount->account_subsection = $accountSubsection;
        $newAccount->account_name = $accountName;
        $newAccount->save();

        return redirect()->route('account_page')
            ->with('success', 'Akun ' . $accountName . ' berhasil dibuat dengan kode: ' . $newAccountCode);
    }
    public function edit_account($accountCode) // Corrected method name
    {
        $account = ChartOfAccount::where('account_code', $accountCode)->firstOrFail();
        return view('AccountCode.accountCode_edit', compact('account'));
    }
    public function update_account(Request $request, $accountCode)
    {
        $account = ChartOfAccount::where('account_code', $accountCode)->firstOrFail();

        // Validation rules
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $account->account_name = $request->input('account_name');
        $account->save();

        return redirect()->route('account_page')->with('success', 'Account updated successfully!');
    }
    public function exportExcel()
    {
        // 1. Fetch the data and build the hierarchy
        $accounts = ChartOfAccount::all();
        $hierarkiAkun = $this->buildHierarchy($accounts);

        // 2. Export the Excel using the generated hierarchical data
        return Excel::download(new AccountCodeExport($hierarkiAkun), 'chart_of_accounts.xlsx');
    }
    public function generatePdf()
    {
        // 1. Fetch the data and build the hierarchy
        $accounts = ChartOfAccount::all();
        $hierarkiAkun = $this->buildHierarchy($accounts);

        // 2. Sort the data (same sorting logic as in your view)
        $sortedHierarkiAkun = collect($hierarkiAkun)
            ->map(function ($accountSections) {
                return collect($accountSections)
                    ->map(function ($accountSubsections) {
                        if (is_array($accountSubsections)) {
                            return collect($accountSubsections)
                                ->map(function ($accountNames) {
                                    if (is_array($accountNames)) {
                                        return collect($accountNames)->sortBy(function ($account) {
                                            if (is_array($account) && isset($account[1])) {
                                                $parts = explode('.', $account[1]);
                                                return end($parts) * 1;
                                            }
                                            return null;
                                        })->toArray();
                                    }
                                    return $accountNames;
                                })->toArray();
                        }
                        return $accountSubsections;
                    })->toArray();
            })->toArray();

        // 3.  Generate the PDF
        $pdf = Pdf::loadView('AccountCode.accountCode_pdf', [
            'hierarkiAkun' => $sortedHierarkiAkun,
        ]);

        // 4.  Download the PDF
        return $pdf->download('chart_of_accounts.pdf');
    }
}
