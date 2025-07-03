<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_menu',
        'user_id',
        'no_resi',
        'jumlah',
        'total_harga',
        'status',
        'asal_provinsi_id',
        'asal_kota_id',
        'alamat_asal',
        'tujuan_provinsi_id',
        'tujuan_kota_id',
        'alamat_tujuan',
        'ekspedisi',
        'jenis_layanan',
        'biaya',
        // 'photo',
    ];

    protected static function generateNoResi()
    {
        $prefix = 'RESI-' . now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return $prefix . '-' . $random;
    }

    public function kirim()
    {
        return $this->hasOne(Kirim::class, 'id_order');
    }
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function asalProvinsi() {
        return $this->belongsTo(Province::class, 'asal_provinsi_id');
    }

    public function asalKota() {
        return $this->belongsTo(City::class, 'asal_kota_id');
    }

    public function tujuanProvinsi() {
        return $this->belongsTo(Province::class, 'tujuan_provinsi_id');
    }

    public function tujuanKota() {
        return $this->belongsTo(City::class, 'tujuan_kota_id');
    }
}
