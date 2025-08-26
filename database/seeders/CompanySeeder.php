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
                'company_name' => 'Properti Indo',
                'address' => 'Jl. Hanjawar Pacet Sukanagalih, Pacet Cipanas - Cianjur',
                'phone' => '0857824567890',
                'email' => 'Propertindo@gmail.com',
                'director' => 'Mr. John Doe',
                'logo' => null, // 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
