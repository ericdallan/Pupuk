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
                'company_name' => 'CV. Sari Pratama Mandiri',
                'address' => 'Jl. Mariwati no 78 Cibadak - Sukaresmi',
                'phone' => '0263-514894',
                'email' => 'SariPratamaMandiri@gmail.com',
                'director' => 'Sari Novianti',
                'logo' => null, // 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
