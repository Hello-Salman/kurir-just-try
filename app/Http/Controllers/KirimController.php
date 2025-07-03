<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Kirim;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class KirimController extends Controller
{
    public function beriRating(Request $request)
    {
        try {
            $validated = $request->validate([
                'no_resi' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
                'ulasan' => 'required|string|max:500'
            ]);

            // Hapus baris log debug
            // Log::info('Rating data:', $validated);

            $input = Order::where('no_resi', $validated['no_resi'])->first();

            if (!$input) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan no resi tersebut tidak ditemukan'
                ], 404);
            }

            if (!empty($input->rating)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating sudah pernah diberikan'
                ], 400);
            }

            $input->update([
                'rating' => $validated['rating'],
                'ulasan' => $validated['ulasan'],
                'tanggal_rating' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Atau pakai error_log() biasa
            error_log('Error saving rating: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tampilkan semua data pengiriman
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);

        $data = Kirim::with(['kurir.user', 'order.menu','order.user','order.tujuanKota'])
            ->when($request->search, function ($query, $search) {
                $query->where('id', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%");
                    // ->orWhereHas('order', function($q) use ($search) {
                    //     $q->where('no_resi', 'like', "%$search%")
                    //       ->orWhere('nama_penerima', 'like', "%$search%");
                    // })
                    // ->orWhereHas('kurir', function($q) use ($search) {
                    //     $q->where('name', 'like', "%$search%")
                    //       ->orWhereHas('user', function($qu) use ($search) {
                    //           $qu->where('name', 'like', "%$search%");
                    //       });
                    // });
            })
            ->latest()
            ->paginate($per);

        $no = ($data->currentPage()-1) * $per + 1;
        foreach($data as $item){
            $item->no = $no++;
        }

        return response()->json($data);

        // $user = $request->user();
        // $query = Kirim::with(['kurir.user', 'order.menu', 'order.user', 'order.tujuanKota']);

        // if ($user->role == 'kurir') {
        //     $query->whereHas('kurir', function ($q) use ($user) {
        //         $q->where('user_id', $user->id);
        //     });
        // }
        // return $query->paginate(10);
    }

    // Simpan data pengiriman baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_order' => 'required|exists:orders,id',
            'id_kurir' => 'required|exists:kurirs,id',
            'status' => 'required|string|in:Dikirim,Terkirim',
        ]);

        $kirim = Kirim::create($validated);
        $kirim->load(['kurir.user', 'order']);

        // Update status order menjadi 'dikirim'
        $order = Order::find($request->id_order);
        if ($order) {
            $order->status = 'dikirim';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'data' => $kirim,
            'message' => 'Data pengiriman berhasil disimpan.',
        ]);
    }

    // Tampilkan detail pengiriman
    public function show(Kirim $kirim, Request $request)
    {
        $kirim->load(['order', 'kurir.user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $kirim->id,
                'id_order' => $kirim->id_order,
                'id_kurir' => $kirim->id_kurir,
                'status' => $kirim->status,
                'order' => $kirim->order,
                    'id' => $kirim->order->id,
                    'no_resi' => $kirim->order->no_resi,
                    'alamat_tujuan' => $kirim->order->alamat_tujuan,
                    'jumlah' => $kirim->order->jumlah,
                    'nama_penerima' => $kirim->order->nama_penerima,
                    'total_harga' => $kirim->order->total_harga,
                    'tanggal_order' => $kirim->order->tanggal_order,

                'kurir' => $kirim->kurir,
                    'id' => $kirim->kurir->id,
                    'name' => $kirim->kurir->user->name,
                    'email' => $kirim->kurir->user->email,
                    'phone' => $kirim->kurir->user->phone,
            ]
        ]);
    }

    // Method get untuk pengambilan data edit (sesuai dengan yang dipanggil di Vue)
    public function get($id)
    {
        $kirim = Kirim::with(['order', 'kurir.user'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $kirim->id,
                'id_order' => $kirim->id_order,
                'id_kurir' => $kirim->id_kurir,
                'status' => $kirim->status,
                'order' => $kirim->order,
                    'id' => $kirim->order->id,
                    'no_resi' => $kirim->order->no_resi,
                    'alamat_tujuan' => $kirim->order->alamat_tujuan,
                    'jumlah' => $kirim->order->jumlah,
                    'nama_penerima' => $kirim->order->nama_penerima,
                    'total_harga' => $kirim->order->total_harga,
                    'tanggal_order' => $kirim->order->tanggal_order,

                'kurir' => $kirim->kurir,
                    'id' => $kirim->kurir->id,
                    'name' => $kirim->kurir->user->name,
                    'email' =>$kirim->kurir->user->email,
                    'phone' => $kirim->kurir->user->phone,
            ]
        ]);
    }

    // Update data pengiriman
    public function update(Request $request, Kirim $kirim)
    {
        $validated = $request->validate([
            'id_order' => 'sometimes|required|exists:orders,id',
            'id_kurir' => 'sometimes|required|exists:kurirs,id',
            'status' => 'sometimes|required|string|in:Dikirim,Terkirim',
        ]);

        $kirim->update($validated);
        $kirim->load(['kurir.user', 'order']);

        // Jika status pengiriman diubah menjadi 'Terkirim', update order juga
        if ($request->status === 'Terkirim') {
            $order = Order::find($kirim->id_order);
            if ($order) {
                $order->status = 'Diterima';
                $order->save();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $kirim,
            'message' => 'Data pengiriman berhasil diupdate.',
        ]);
    }

    // Hapus data pengiriman
    public function destroy(Kirim $kirim)
    {
        $kirim->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengiriman berhasil dihapus.'
        ]);
    }
}
