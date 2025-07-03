<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Kirim;

class CekResiController extends Controller
{
    public function cek(Request $request)
    {
        $request->validate([
            'no_resi' => 'required|string',
            'kurir' => 'required|string',
        ]);

        // Cari order berdasarkan no_resi dan kurir
        $order = Order::with(['user', 'menu'])
            ->where('no_resi', $request->no_resi)
            ->where('kurir', $request->kurir)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Resi tidak ditemukan.',
            ], 404);
        }

        // Ambil data pengiriman terakhir jika ada
        $kirim = Kirim::where('id_order', $order->id)
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'no_resi' => $order->no_resi,
                'kurir' => $kirim->kurir,
                'status' => $kirim->status ?? 'Belum Dikirim',
                'pengirim' => $kirim->kurir->user->name ?? '-',
                'penerima' => $order->user->name ?? '-',
                'menu' => $order->menu->nama_menu ?? '-',
                'alamat' => $order->alamat_tujuan ?? '-',
                'history' => $kirim ? [
                    [
                        'status' => $kirim->status,
                        'tanggal' => $kirim->created_at->format('Y-m-d H:i'),
                    ]
                ] : [],
            ],
        ]);
    }
}
