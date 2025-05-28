<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // public function checkout(Request $request)
    // {
    //     $validated = $request->validate([
    //         'items' => 'required|array',
    //         'items.*.harga' => 'required|numeric',
    //         'items.*.jumlah' => 'required|string',
    //     ]);

    //     $total = 0;
    //     foreach ($validated['items'] as $item) {
    //         $total += $item['harga'] * $item['jumlah'];
    //     }

    //     return response()->json([
    //         'total_harga' => $total,
    //     ]);
    // }
    public function getProvinces()
    {
        // $response = Http::withHeaders([
        //     'key' => env('RAJAONGKIR_API_KEY'),
        // ])->get('https://api.rajaongkir.com/starter/province');

        // $provinces = collect($response['rajaongkir'])
        //     ->pluck('province', 'province_id');
        $provinces = Province::all()->pluck('name','id');

        return response()->json($provinces);
    }

    // Ambil daftar kota berdasarkan provinsi
    public function getCities($provinceId)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
        ])->get('https://api.rajaongkir.com/starter/city?province=' . $provinceId);

        Log::info("Cities", $response['rajaongkir']);

        $cities = collect($response['rajaongkir'])
            ->pluck('city_name', 'city_id');



        return response()->json($cities);
    }

    public function hitungOngkir(Request $request)
    {
        Log::info("Request Cost", $request->all()); // log request untuk debugging
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
        ])->post('https://api.rajaongkir.com/starter/cost', [
            'origin' => $request->origin,
            'destination' => $request->destination,
            'weight' => $request->weight,
            'courier' => $request->courier,
        ]);

        $responseData = $response->json(); // Ubah response menjadi array

        Log::info("Cost", $responseData['rajaongkir']); // log dulu untuk cek struktur

        $cost = $responseData['rajaongkir']['results'][0]['costs'];

        Log::info("Cost Results", $cost); // log hasil biaya ongkir

        return response()->json($cost);
    }


    /**
     * Menampilkan daftar order dengan pagination.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);

        $data = Order::with(['menu', 'kurir.user', 'asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota']) // Muat relasi menu dan kurir
            ->when($request->search, function ($query, $search) {
                $query->where('id', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('jumlah', 'like', "%$search%")
                    ->orWhere('total_harga', 'like', "%$search%")
                    ->orWhere('ekspedisi', 'like', "%$search%")
                    ->orWhere('jenis_layanan', 'like', "%$search%")
                    ->orWhere('alamat_asal', 'like', "%$search%")
                    ->orWhere('alamat_tujuan', 'like', "%$search%");
            })
            ->latest()
            ->paginate($per);

        $no = ($data->currentPage() - 1) * $per + 1;
        foreach ($data as $item) {
            $item->no = $no++;
        }

        return response()->json($data);
    }

    /**
     * Menyimpan order baru.
     */
    public function store(Request $request)
    {

        $order = $request->validate([
            'id_menu' => 'required|exists:menus,id', // Validasi ID menu harus ada di tabel menus
            'jumlah' => 'required|numeric|min:1', // Validasi harga harus berupa angka dan minimal 1
            'total_harga' => 'required|numeric|min:0', // Validasi total_harga harus berupa angka dan minimal 0
            'ekspedisi' => 'required|string',
            'jenis_layanan' => 'required|string',
            'biaya' => 'required|integer',

            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,id',
            'alamat_tujuan' => 'required|string',

            'berat_barang' => 'required|numeric|min:0.01',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,id',
            'alamat_asal' => 'required|string',

        ]);
        Log::info("Request", $request->all()); // log request untuk debugging
        $menu = Menu::findOrFail($request->id_menu);
        $totalHarga = $menu->harga * $request->jumlah;


        $order = Order::create([
            'jenis_layanan' => $request->jenis_layanan,
            'biaya' => $request->biaya,
            'ekspedisi' => $request->ekspedisi,
            'berat_barang' => $request->berat_barang, // pastikan field DB kamu memang 'berat_barang'
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'asal_provinsi_id' => $request->asal_provinsi_id,
            'asal_kota_id' => $request->asal_kota_id,
            'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            'tujuan_kota_id' => $request->tujuan_kota_id,
            'id_menu' => $request->id_menu, // ID menu yang dipilih
            'jumlah' => $request->jumlah, // Jumlah item yang dipesan
            'total_harga' => $totalHarga,

            // 'berat_barang' => $request->berat_barang,
            // 'alamat_asal' => $request->alamat_asal,
            // 'alamat_tujuan' => $request->alamat_tujuan,
            // 'status' => $request->status,
            // 'ekspedisi' => $request->ekspedisi,
            // 'layanan' => $request->layanan,
            // 'biaya' => $request->biaya,
            // 'asal_provinsi_id' => $request->asal_provinsi_id,
            // 'asal_kota_id' => $request->asal_kota_id,
            // 'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            // 'tujuan_kota_id' => $request->tujuan_kota_id,

        ]);
        // $order->load('menu', 'kurir.user', 'asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota');

        return response()->json([
            'data' => $order,
            'message' => 'Order berhasil dibuat.',
        ]);
    }

    // Ambil harga otomatis dari menu
// $menu = Menu::findOrFail($validatedData['id_menu']);

// $validatedData['harga'] = $menu->harga;
// $validatedData['total_harga'] = $menu->harga * $validatedData['jumlah'];

    // Jika pakai foto
    // if ($request->hasFile('photo')) {
    //     $data['photo'] = $request->file('photo')->store('photo', 'public');
    // }



    /**
     * Memperbarui data order.
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->validated([
            'id_menu' => 'required|exists:menus,id', // Validasi ID menu harus ada di tabel menus
            'status' => 'required|string|in:on_delivery,selesai,batal', // Validasi status hanya menerima nilai tertentu
            'jumlah' => 'required|numeric|min:1', // Validasi harga harus berupa angka dan minimal 0
        ]);

        $user = auth()->user();
        $kurirId = $user->kurir->id ?? null;

        if ($user->role === 'kurir') {
            // Jika transaksi sudah memiliki id_kurir yang berbeda, berarti pesanan sudah diambil oleh kurir lain
            if ($order->id_kurir && $order->id_kurir != $kurirId) {
                // Jika sudah diambil oleh kurir lain, maka return respons error
                return response()->json([
                    'message' => 'Pesanan sudah diambil oleh kurir lain.' // Pesanan tidak bisa diubah oleh kurir lain
                ], 403); // Mengembalikan status kode 403 (Forbidden)
            }
        }

        $order->update($data);
        $order->load('menu');

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diperbarui.',
            'data' => [
                'id' => $order->id,
                'id_menu' => $order->menu->id,
                'jumlah' => $order->jumlah,
                'harga' => $order->menu->harga,
                'total_harga' => $order->total_harga,
                'status' => $order->status,
                'id_kurir' => $order->id_kurir,
            ],
        ]);
    }

    /**
     * Menampilkan order berdasarkan ID.
     */
    public function show(Order $order)
    {
        $order->load('menu', 'kurir'); // Muat relasi menu dan pelanggan

        return response()->json([
        //     'success' => true,
        //     'order' => [
                'id' => $order->id,
                'id_menu' => $order->id_menu,
                'jumlah' => $order->jumlah,
                'harga' => $order->menu->harga,
                'total_harga' => $order->total_harga,
                'status' => $order->status,
            // ],
        ]);
    }

    public function get($id)
    {
        $order = Order::with(['menu', 'asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota'])
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Menghapus order berdasarkan ID.
     */
    public function destroy(Order $order)
    {

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dihapus.',
        ]);
    }
}
