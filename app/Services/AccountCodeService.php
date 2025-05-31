<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AccountCodeService
{
    /**
     * Build hierarchical structure of accounts
     *
     * @param \Illuminate\Database\Eloquent\Collection $accounts
     * @return array
     */
    public function buildHierarchy($accounts)
    {
        $hierarki = [];

        foreach ($accounts as $account) {
            $accountType = $account->account_type;
            $accountSection = $account->account_section;
            $accountSubsection = $account->account_subsection;
            $accountName = $account->account_name;
            $accountCode = $account->account_code;

            $hierarki[$accountType] = ($hierarki[$accountType] ?? []);
            $hierarki[$accountType][$accountSection] = ($hierarki[$accountType][$accountSection] ?? []);

            if ($accountSubsection) {
                $hierarki[$accountType][$accountSection][$accountSubsection] = 
                    ($hierarki[$accountType][$accountSection][$accountSubsection] ?? []);
                
                $hierarki[$accountType][$accountSection][$accountSubsection][] = 
                    $accountCode ? [$accountName, $accountCode] : $accountName;
            } else {
                $hierarki[$accountType][$accountSection][] = 
                    $accountCode ? [$accountName, $accountCode] : $accountName;
            }
        }

        return $hierarki;
    }

    /**
     * Generate new account code
     *
     * @param array $data
     * @return string
     * @throws ValidationException
     */
    public function generateAccountCode(array $data)
    {
        $validator = Validator::make($data, [
            'account_type' => 'required|string',
            'account_section' => 'required|string',
            'account_subsection' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $prefix = $this->generatePrefix(
            $data['account_type'],
            $data['account_section'],
            $data['account_subsection']
        );

        if (!$prefix) {
            throw new \Exception('Failed to generate account code prefix.');
        }

        $lastAccount = ChartOfAccount::where('account_code', 'like', $prefix . '.%')
            ->orderBy('account_code', 'desc')
            ->first();

        $lastIncrement = 0;
        if ($lastAccount) {
            $parts = explode('.', $lastAccount->account_code);
            $lastIncrement = (int) end($parts);
        }

        $newIncrement = $lastIncrement + 1;

        if ($newIncrement > 99) {
            throw new \Exception('Maximum account number limit (99) reached for this combination.');
        }

        return $prefix . '.' . str_pad($newIncrement, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Create new account
     *
     * @param array $data
     * @return ChartOfAccount
     * @throws ValidationException
     */
    public function createAccount(array $data)
    {
        $validator = Validator::make($data, [
            'account_type' => 'required|string',
            'account_section' => 'required|string',
            'account_subsection' => 'required|string',
            'account_name' => 'required|string|max:255|unique:chart_of_accounts,account_name',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $prefix = $this->generatePrefix(
            $data['account_type'],
            $data['account_section'],
            $data['account_subsection']
        );

        if (!$prefix) {
            throw new \Exception('Failed to generate account code prefix.');
        }

        $existingAccounts = ChartOfAccount::where('account_code', 'like', $prefix . '.%')
            ->orderBy('account_code')
            ->get();

        $usedIncrements = $existingAccounts->map(function ($account) {
            $parts = explode('.', $account->account_code);
            return count($parts) === 4 ? (int) end($parts) : null;
        })->filter()->toArray();

        $availableIncrement = null;
        for ($i = 1; $i <= 99; $i++) {
            if (!in_array($i, $usedIncrements)) {
                $availableIncrement = $i;
                break;
            }
        }

        if ($availableIncrement === null) {
            throw new \Exception('Maximum account number limit (99) reached for this combination.');
        }

        $newAccountCode = $prefix . '.' . str_pad($availableIncrement, 2, '0', STR_PAD_LEFT);

        return ChartOfAccount::create([
            'account_code' => $newAccountCode,
            'account_type' => $data['account_type'],
            'account_section' => $data['account_section'],
            'account_subsection' => $data['account_subsection'], // Perbaikan typo dari 'account liberdade'
            'account_name' => $data['account_name'],
        ]);
    }

    /**
     * Generate account code prefix
     *
     * @param string $type
     * @param string $section
     * @param string $subsection
     * @return string|null
     */
    protected function generatePrefix($type, $section, $subsection)
    {
        $typeCode = $this->getTypeCode($type);
        $sectionCode = $this->getSectionCode($section, $type);
        $subsectionCode = $this->getSubsectionCode($subsection, $type, $section);

        if ($typeCode && $sectionCode && $subsectionCode) {
            return implode('.', [$typeCode, $sectionCode, $subsectionCode]);
        }

        return null;
    }

    /**
     * Get type code mapping
     *
     * @param string $typeName
     * @return string|null
     */
    protected function getTypeCode($typeName)
    {
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

    /**
     * Get section code mapping
     *
     * @param string $sectionName
     * @param string $accountType
     * @return string|null
     */
    protected function getSectionCode($sectionName, $accountType)
    {
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
                'Harga Pokok Pembelian Barang Dagangan' => '1',
                'Harga Pokok Pembelian Barang Jadi' => '2',
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
                'Beban Pajak' => '3',
            ],
        ];

        return $sections[$accountType][$sectionName] ?? null;
    }

    /**
     * Get subsection code mapping
     *
     * @param string $subsectionName
     * @param string $accountType
     * @param string $accountSection
     * @return string|null
     */
    protected function getSubsectionCode($subsectionName, $accountType, $accountSection)
    {
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
                'Harga Pokok Pembelian Barang Dagangan' => [
                    'Harga Pokok Pembelian Barang Dagangan' => '01',
                ],
                'Harga Pokok Pembelian Barang Jadi' => [
                    'Harga Pokok Pembelian Barang Jadi' => '01',
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
                    'Beban Pajak' => '01',
                ],
            ],
        ];

        return $subsections[$accountType][$accountSection][$subsectionName] ?? null;
    }
}