<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08123456789',
        ])->assignRole('admin');

        User::create([
            'name' => 'Kurir',
            'email' => 'kurir@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08987654321',
        ])->assignRole('kurir');

        User::create([
            'name' => 'Pelanggan',
            'email' => 'user@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '081987654321',
        ])->assignRole('pelanggan');
    }
}
