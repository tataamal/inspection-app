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
            'name' => 'autoemail',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'inspector',
            'password' => Hash::make('password'),
            'role' => 'inspector'
        ]);

        User::create([
            'name' => 'buyer',
            'password' => Hash::make('password'),
            'role' => 'buyer'
        ]);
    }
}
