<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Admin; // Pastikan Anda mengimpor model Admin Anda
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('admins')->insert([
            'name' => 'Super Admin',
            'email' => 'super@admin.net',
            'password' => Hash::make('verysecret'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('masters')->insert([
            'name' => 'Master Admin',
            'email' => 'Master@admin.net',
            'password' => Hash::make('VerySecretAdmin'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
