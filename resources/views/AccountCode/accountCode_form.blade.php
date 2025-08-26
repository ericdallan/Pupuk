<div class="modal fade" id="accountModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="accountModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-l">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="accountModalLabel">Buat Akun Baru</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="accountForm" action="{{ route('account_create') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="account_type" class="form-label">Tipe Akun</label>
                        <select class="form-select" id="account_type" name="account_type" required>
                            <option value="">Pilih Tipe Akun</option>
                            <option value="ASET">ASET</option>
                            <option value="KEWAJIBAN">KEWAJIBAN</option>
                            <option value="EKUITAS">EKUITAS</option>
                            <option value="PENDAPATAN_USAHA">PENDAPATAN USAHA</option>
                            <option value="HARGA_POKOK_PRODUKSI_DAN_PENJUALAN">HARGA POKOK PRODUKSI DAN PENJUALAN
                            </option>
                            <option value="BEBAN_BEBAN_USAHA">BEBAN-BEBAN USAHA</option>
                            <option value="PENDAPATAN_DAN_BEBAN_LAIN_LAIN">PENDAPATAN DAN BEBAN LAIN-LAIN</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="account_section" class="form-label">Bagian Akun</label>
                        <select class="form-select" id="account_section" name="account_section" required>
                            <option value="">Pilih Bagian Akun</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="account_subsection" class="form-label">Anak Bagian Akun</label>
                        <select class="form-select" id="account_subsection" name="account_subsection" required>
                            <option value="">Pilih Anak Bagian Akun</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="account_name" class="form-label">Nama Akun</label>
                        <input type="text" class="form-control" id="account_name" name="account_name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="saveAccountBtn">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const tipeAkunSelect = document.getElementById('account_type');
    const bagianAkunSelect = document.getElementById('account_section');
    const anakBagianAkunSelect = document.getElementById('account_subsection');
    const accountForm = document.getElementById('accountForm');

    const hierarkiAkun = {
        ASET: {
            'Aset Lancar': {
                'Kas dan Setara Kas': ['Kas Tunai', 'Kas di Bank BSI', 'Kas di Bank Mandiri', 'Deposito <= 3',
                    'Setara Kas Lainnya'
                ],
                'Piutang': ['Piutang Usaha', 'Piutang kepada Pegawai', 'Piutang Lainnya'],
                'Penyisihan Piutang': ['Penyisihan Piutang Usaha Tak Tertagih'],
                'Persediaan': ['Persediaan Barang Dagangan', 'Persediaan Bahan Baku',
                    'Persediaan Barang Dalam Proses', 'Persediaan Barang Jadi'
                ],
                'Pembayaran Dimuka': ['Sewa Dibayar Dimuka', 'Asuransi Dibayar Dimuka', 'PPh 25', 'PPN Masukan'],
                'Aset Lancar Lainnya': ['Aset Lancar Lainnya']
            },
            Investasi: {
                Investasi: ['Deposito > 3 bulan', 'Investasi Lainnya']
            },
            'Aset Tetap': {
                'Aset Tetap': ['Tanah', 'Kendaraan', 'Peralatan dan Mesin', 'Meubelair', 'Gedung dan Bangunan',
                    'Konstruksi Dalam Pengerjaan'
                ],
                'Akumulasi Penyusutan Aset Tetap': ['Akumulasi Penyusutan Kendaraan',
                    'Akumulasi Penyusutan Peralatan dan Mesin', 'Akumulasi Penyusutan Meubelair',
                    'Akumulasi Penyusutan Gedung dan Bangunan'
                ]
            }
        },
        KEWAJIBAN: {
            'Kewajiban Jangka Pendek': {
                'Utang Usaha': ['Utang Usaha'],
                'Utang Pajak': ['PPN Keluaran', 'PPh 21', 'PPh 23', 'PPh 29'],
                'Utang Gaji/Upah dan Tunjangan': ['Utang Gaji/Upah dan Tunjangan'],
                'Utang Utilitas': ['Utang Listrik', 'Utang Telepon/Internet'],
                'Pendapatan diterima di Muka': ['Uang muka dari Pelanggan'],
                'Utang Jangka Pendek Lainnya': ['Utang Jangka Pendek Lainnya']
            },
            'Kewajiban Jangka Panjang': {
                'Utang Ke Bank': ['Utang Ke Bank'],
                'Utang Jangka Panjang Lainnya': ['Utang Jangka Panjang Lainnya']
            }
        },
        EKUITAS: {
            'Modal Pemilik': ['Modal Pemilik'],
            'Pengambilan oleh Pemilik': ['Pengambilan oleh Pemilik'],
            'Saldo Laba': ['Saldo Laba', 'Saldo Laba Tidak Dicadangkan'],
            'Ikhtisar Laba Rugi': ['Ikhtisar Laba Rugi']
        },
        PENDAPATAN_USAHA: {
            'Pendapatan Penjualan Bahan Baku': {
                'Pendapatan Penjualan Bahan Baku': [],
            },
            'Pendapatan Penjualan Barang Jadi': {
                'Pendapatan Penjualan Barang Jadi': [],
            },
            'Pendapatan Sewa': {
                'Pendapatan Sewa': [],
            }
        },
        HARGA_POKOK_PRODUKSI_DAN_PENJUALAN: {
            'Harga Pokok Penjualan Barang Dagangan': ['Harga Pokok Penjualan Barang Dagangan'],
            'Harga Pokok Penjualan Barang Jadi': ['Harga Pokok Penjualan Barang Jadi'],
            'Harga Pokok Produksi': {
                'Biaya Bahan Baku': ['Biaya Bahan Baku'],
                'Beban Upah Langsung': ['Beban Upah dan Tunjangan Bag. Produksi',
                    'Beban Lembur, Insentif (Bonus) Bag. Produksi'
                ],
                'Biaya Overhead': ['Beban Pemeliharaan dan Perbaikan Peralatan Kantor',
                    'Beban Pemeliharaan dan Perbaikan Mesin', 'Beban Perlengkapan Produksi',
                    'Beban Listrik Pabrik'
                ]
            }
        },
        BEBAN_BEBAN_USAHA: {
            'Beban Administrasi dan Umum': {
                'Beban Pegawai Bagian Administrasi Umum': ['Beban Gaji dan Tunjangan Bag. Adum',
                    'Beban Insentif (Bonus) Bag. Adum', 'Beban Seragam Pegawai Bag. Adum',
                    'Beban Pegawai Bag. Adum Lainnya'
                ],
                'Beban Perlengkapan': ['Beban Alat Tulis Kantor (ATK)', 'Beban Foto Copy dan Cetak',
                    'Beban Konsumsi', 'Beban Perlengkapan Lainnya'
                ],
                'Beban Pemeliharaan dan Perbaikan Peralatan Kantor': [
                    'Beban Pemeliharaan dan Perbaikan Peralatan Kantor'
                ],
                'Beban Utilitas': ['Beban Listrik Kantor', 'Beban Telepon/Internet', 'Beban Utilitas Lainnya'],
                'Beban Sewa dan Asuransi': ['Beban Sewa', 'Beban Asuransi'],
                'Beban Kebersihan dan Keamanan': ['Beban Kebersihan', 'Beban Keamanan'],
                'Beban Penyisihan dan Penyusutan/Amortisasi': ['Beban Penyisihan Piutang Tak Tertagih',
                    'Beban Penyusutan Kendaraan', 'Beban Penyusutan Peralatan dan Mesin',
                    'Beban Penyusutan Meubelair', 'Beban Penyusutan Gedung dan Bangunan',
                    'Beban Amortisasi Aset tak berwujud'
                ],
                'Beban Administrasi dan Umum Lainnya': ['Beban BBM, Parkir, Toll', 'Beban Audit',
                    'Beban Perjalanan Dinas', 'Beban Transportasi', 'Beban Jamuan Tamu',
                    'Beban Administrasi dan Umum Lainnya'
                ]
            },
            'Beban Operasional': {
                'Beban Pegawai Bagian Operasional': ['Beban Gaji/Upah Bag. Operasional',
                    'Beban Uang Makan Bag. Operasional'
                ],
                'Beban Pemeliharaan dan Perbaikan': ['Beban Perbaikan dan Renovasi'],
                'Beban Operasional Lainnya': ['Beban Operasional Lainnya']
            },
            'Beban Pemasaran': {
                'Beban Pegawai Bagian Pemasaran': ['Beban Gaji/Upah Bag. Pemasaran',
                    'Beban Insentif (Bonus) Bag. Pemasaran', 'Beban Seragam Pegawai Bag. Pemasaran'
                ],
                'Beban Pemasaran Lainnya': ['Beban Pemasaran Lainnya']
            },
            'Beban Pajak': ['Beban PPh 21', 'Beban PPh 23', 'Beban PPh 25', 'Beban PPh 29', 'Beban PPh Final',
                'Beban Pajak Lainnya'
            ]
        },
        PENDAPATAN_DAN_BEBAN_LAIN_LAIN: {
            'Pendapatan Lain-lain': {
                'Pendapatan dari Bank': ['Pendapatan Bunga Bank'],
                'Pendapatan Penjualan Aset Tetap': ['Keuntungan Penjualan Aset Tetap'],
                'Pendapatan Lain-lain lainnya': ['Pendapatan Lain-lain lainnya']
            },
            'Beban Lain-lain': {
                'Beban Bank': ['Beban Administrasi Bank'],
                'Beban Bunga': ['Beban Bunga'],
                'Beban Penjualan Aset Tetap': ['Kerugian Penjualan Aset Tetap'],
                'Beban Lain-lain lainnya': ['Beban Lain-lain lainnya']
            },
        }
    };

    tipeAkunSelect.addEventListener('change', () => {
        bagianAkunSelect.innerHTML = '<option value="">Pilih Bagian Akun</option>';
        anakBagianAkunSelect.innerHTML = '<option value="">Pilih Anak Bagian Akun</option>';

        const tipeAkun = tipeAkunSelect.value;
        if (tipeAkun && hierarkiAkun[tipeAkun]) {
            Object.keys(hierarkiAkun[tipeAkun]).forEach(bagianAkun => {
                const option = document.createElement('option');
                option.value = bagianAkun;
                option.textContent = bagianAkun;
                bagianAkunSelect.appendChild(option);
            });
        }
    });

    bagianAkunSelect.addEventListener('change', () => {
        anakBagianAkunSelect.innerHTML = '<option value="">Pilih Anak Bagian Akun</option>';

        const tipeAkun = tipeAkunSelect.value;
        const bagianAkun = bagianAkunSelect.value;
        if (bagianAkun && hierarkiAkun[tipeAkun] && hierarkiAkun[tipeAkun][bagianAkun]) {
            const anakBagian = hierarkiAkun[tipeAkun][bagianAkun];
            if (Array.isArray(anakBagian)) {
                anakBagian.forEach(namaAnakBagian => {
                    const option = document.createElement('option');
                    option.value = namaAnakBagian;
                    option.textContent = namaAnakBagian;
                    anakBagianAkunSelect.appendChild(option);
                });
            } else if (typeof anakBagian === 'object') {
                Object.keys(anakBagian).forEach(namaAnakBagian => {
                    const option = document.createElement('option');
                    option.value = namaAnakBagian;
                    option.textContent = namaAnakBagian;
                    anakBagianAkunSelect.appendChild(option);
                });
            }
        }
    });
</script>
