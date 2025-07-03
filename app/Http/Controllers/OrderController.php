<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
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

        $data = Order::with(['menu', 'user', 'asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota']) // Muat relasi menu dan kurir
        ->when($request->search, function ($query, $search) {
            $query->where('id', 'like', "%$search%")
                    ->orwhere('no_resi', 'like', "%$search%")
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
            'user_id' => 'required|exists:users,id', // Validasi ID user harus ada di tabel users
            'ekspedisi' => 'required|string',
            'jenis_layanan' => 'required|string',
            'biaya' => 'required|integer',

            'tujuan_provinsi_id' => 'required|exists:provinces,id',
            'tujuan_kota_id' => 'required|exists:cities,city_id',
            'alamat_tujuan' => 'required|string',

            'berat_barang' => 'required|numeric|min:0.01',
            'asal_provinsi_id' => 'required|exists:provinces,id',
            'asal_kota_id' => 'required|exists:cities,city_id',
            'alamat_asal' => 'required|string',

        ]);
        Log::info("Request", $request->all()); // log request untuk debugging
        $menu = Menu::findOrFail($request->id_menu);
        $totalHarga = $menu->harga * $request->jumlah;

        $cityIdtujuan = City::where('city_id', $request->tujuan_kota_id)->first();
        $cityIdasal = City::where('city_id', $request->asal_kota_id)->first();


        $order = Order::create([
            'jenis_layanan' => $request->jenis_layanan,
            'no_resi' => 'JTX-' . strtoupper(uniqid()),
            'biaya' => $request->biaya,
            'ekspedisi' => $request->ekspedisi,
            'berat_barang' => $request->berat_barang, // pastikan field DB kamu memang 'berat_barang'
            'alamat_asal' => $request->alamat_asal,
            'alamat_tujuan' => $request->alamat_tujuan,
            'asal_provinsi_id' => $request->asal_provinsi_id,
            'asal_kota_id' => $cityIdasal->id, // ID kota asal
            'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
            'tujuan_kota_id' => $cityIdtujuan->id, // ID kota tujuan
            'id_menu' => $request->id_menu, // ID menu yang dipilih
            'jumlah' => $request->jumlah, // Jumlah item yang dipesan
            'total_harga' => $totalHarga,
            'user_id' => $request->user_id, // ID user yang membuat order
        ]);

        return response()->json([
            'success' => true,
            'id' => $order->id,
            'data' => $order,
            'no_resi' => $order->no_resi,
            'message' => 'Order berhasil dibuat.',
        ]);
    }

    /**
         * Memperbarui data order.
         */
        public function update(Request $request, Order $order)
        {
            $data = $request->validated([
                'id_menu' => 'required|exists:menus,id', // Validasi ID menu harus ada di tabel menus
                'status' => 'required|string|in:menunggu,dikirim,diterima,batal', // Validasi status hanya menerima nilai tertentu
                'jumlah' => 'required|numeric|min:1', // Validasi harga harus berupa angka dan minimal 0
            ]);

            $user = auth()->user();
            $userId = $user->id ?? null;

            if ($user->role === 'user') {
                // Jika transaksi sudah memiliki id_kurir yang berbeda, berarti pesanan sudah diambil oleh kurir lain
            if ($order->user_id && $order->user_id != $userId) {
                // Jika sudah diambil oleh kurir lain, maka return respons error
                return response()->json([
                    'message' => 'Sudah dipesan.' // Pesanan tidak bisa diubah oleh kurir lain
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
                // 'id_kurir' => $order->id_kurir,

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
                // 'total_harga' => $totalHarga,
            ],
        ]);
    }

    /**
     * Menampilkan order berdasarkan ID.
     */
    public function show($id, Request $request)
    {
        $order = Order::find($id);
        $order->load('menu', 'user'); // Muat relasi menu dan pelanggan

        Log::info("Order",['Order' => $order]);
        Log::info("id menu : ",["id"=>$order->id_menu]);


        return response()->json($order);
    }

        public function get()
        {
            $order = Order::where('status','menunggu')->get();

            return response()->json([
                'success' => true,
                'data' => Order::with("menu")->get(),
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

        public function __construct()
        {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');
        }

        public function createCharge(Request $request)
        {
            $request->validate([
                'total_harga' => 'required|numeric|min:100',
                // 'detail_produk' => 'required|array|min:1',
            ]);

            $orderId = 'ORDER-' . uniqid();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $request->total_harga,
                ],
                'item_details' => $request->detail_produk,

                 'customer_details' => [
                    'first_name' => $request->input('pengirim') ?? 'User',
                    'email' => Auth::user()->email ?? 'default@email.com',
                    'phone' => Auth::user()->phone ?? '08123456789',
                ],

                'expiry' => [
                    'start_time' => date("Y-m-d H:i:s O"),
                    'duration' => 5,
                    'unit' => 'minute'
                ]
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                return response()->json([
                    'snap_token' => $snapToken,
                    'order_id' => $orderId
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage()
                ], 500);
            }
        }

}




    // 'berat_barang' => $request->berat_barang,
    // 'alamat_asal' => $request->alamat_asal,
    // 'alamat_tujuan' => $request->alamat_tujuan,
    // 'status' => $request->status,
    // 'ekspedisi' => $request->ekspedisi,
    // 'layanan' => $request->layanan,
    // 'biaya' => $request->biaya,
    // 'tujuan_provinsi_id' => $request->tujuan_provinsi_id,
    // 'tujuan_kota_id' => $request->tujuan_kota_id,

    // if (!$order->no_resi) {
        //         $order->no_resi = 'RESI-' . strtoupper(uniqid());
        //         $order->save();
        //     }
        // $order->load('menu', 'kurir.user', 'asalProvinsi', 'asalKota', 'tujuanProvinsi', 'tujuanKota');

// Ambil harga otomatis dari menu
// $menu = Menu::findOrFail($validatedData['id_menu']);

// $validatedData['harga'] = $menu->harga;
// $validatedData['total_harga'] = $menu->harga * $validatedData['jumlah'];

// Jika pakai foto
// if ($request->hasFile('photo')) {
    //     $data['photo'] = $request->file('photo')->store('photo', 'public');
    // }
    // public function checkout(Request $request)
    // 'asal_provinsi_id' => $request->asal_provinsi_id,
    // 'asal_kota_id' => $request->asal_kota_id,
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
