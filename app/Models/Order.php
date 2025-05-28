<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_menu',
        'id_kurir',
        'jumlah',
        'total_harga',
        'status',
        'asal_provinsi_id',
        'asal_kota_id',
        'tujuan_provinsi_id',
        'tujuan_kota_id',
        'ekspedisi',
        'jenis_layanan',
        'biaya',
        // 'photo',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'id_kurir');
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
