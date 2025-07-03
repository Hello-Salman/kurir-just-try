<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurir extends Model
{
    use HasFactory;

    protected $table = 'kurirs';

    protected $fillable = [
        'id',
        'user_id',
        'status',
        // 'photo', // Jika kamu upload gambar ke storage
    ];

    /**
     * Relasi: Kurir dimiliki oleh satu User
     */
    // File: app/Models/Kurir.php

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kirim()
    {
        return $this->hasOne(Kirim::class, 'id_menu');
    }
}
