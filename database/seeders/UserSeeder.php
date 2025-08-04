<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'auto_email',
            'password' => Hash::make('11223344'),
            'role' => 'admin'
        ]);

        User::create([
            'username' => 'auto_email',
            'password' => Hash::make('11223344'),
            'role' => 'inspector'
        ]);

        User::create([
            'username' => 'auto_email',
            'password' => Hash::make('11223344'),
            'role' => 'buyer'
        ]);
    }
}
