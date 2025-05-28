<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->truncate();

        Setting::create([
            'app' => 'AntaRasa',
            'description' =>  'Web Kurir Makanan',
            'logo' =>  '/media/logo.png',
            'bg_auth' =>  '/media/bg-auth.jpg',
            'banner' =>  '/media/misc/banner.jpg',
            'pemerintah' =>  'Pemerintah Provinsi Jawa Timur',
            'dinas' =>  'Badan Pengawasan Obat dan Makanan',
            'alamat' =>  '',
            'telepon' =>  '',
            'email' =>  '',
        ]);
    }
}
