<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kirim extends Model
{
    use HasFactory;

    protected $table = 'kirims';
    protected $fillable = [
        'id',
        'id_kurir',
        'id_order',
        'status',
    ];

    public function kurir()
    {
        return $this->belongsTo(Kurir::class, 'id_kurir');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }
}
