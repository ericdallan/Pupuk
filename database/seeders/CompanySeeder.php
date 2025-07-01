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
                'company_name' => 'PD. Sahudi',
                'address' => 'Jl. Hanjawar Pacet Sukanagalih, Pacet Cipanas - Cianjur',
                'phone' => 'Sukanagalih',
                'email' => 'PdSahudi@gmail.com',
                'director' => 'Akang',
                'logo' => null, // 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
