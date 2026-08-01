<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        $password = Hash::make('password');

        // 1. Create Test Accounts
        $pemohonTest = User::create([
            'name' => 'Pemohon Test',
            'email' => 'pemohon@sipdok.test',
            'password' => $password,
            'company_name' => 'PT Test Pemohon Indonesia',
            'company_address' => 'Jl. Test No. 1, Jakarta',
            'phone' => '081234567890',
            'is_active' => true,
        ]);
        $pemohonTest->assignRole('pemohon');

        $penilaiTest = User::create([
            'name' => 'Penilai Test',
            'email' => 'penilai@sipdok.test',
            'password' => $password,
            'company_name' => null,
            'company_address' => null,
            'phone' => '081234567891',
            'is_active' => true,
        ]);
        $penilaiTest->assignRole('penilai');

        // 2. Create Bulk Users
        $totalToCreate = 999;
        $chunkSize = 100;

        echo "Seeding 999 pemohon...\n";
        for ($i = 0; $i < ceil($totalToCreate / $chunkSize); $i++) {
            $count = min($chunkSize, $totalToCreate - ($i * $chunkSize));
            $users = User::factory()->count($count)->pemohon()->create(['password' => $password]);
            foreach ($users as $user) {
                $user->assignRole('pemohon');
            }
        }

        echo "Seeding 999 penilai...\n";
        for ($i = 0; $i < ceil($totalToCreate / $chunkSize); $i++) {
            $count = min($chunkSize, $totalToCreate - ($i * $chunkSize));
            $users = User::factory()->count($count)->penilai()->create(['password' => $password]);
            foreach ($users as $user) {
                $user->assignRole('penilai');
            }
        }
    }
}
