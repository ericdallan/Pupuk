<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert([
            [
                'company_name' => 'Candra Meubel',
                'address' => 'Jl. Raya Puncak - Cianjur No.8, Palasari, Kec. Cipanas, Kabupaten Cianjur, Jawa Barat 43253',
                'phone' => '0818-771-406',
                'email' => 'SariPratamaMandiri@gmail.com',
                'director' => 'Sari Novianti',
                'logo' => null, // 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
