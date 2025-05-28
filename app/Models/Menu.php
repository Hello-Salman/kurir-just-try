<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'id',
        'nama_menu',
        'harga',
        'kategori',
        'status',
        'photo',
    ];

    public function order()
    {
        return $this->hasMany(Order::class, 'id_menu');
    }

    public function kurir()
    {
        return $this->hasMany(Kurir::class, 'id_menu');
    }
}
