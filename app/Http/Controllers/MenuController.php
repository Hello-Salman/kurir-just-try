<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Menu::all(),
            // 'data' => Kurir::select('id', 'name', 'email', 'phone', 'status', 'rating', 'photo')->get()
        ]);
    }
    /**
     * Menampilkan daftar menu menu dengan pagination.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;

        $data = Menu::when($request->search, function ($query, $search) {
                $query->where('nama_menu', 'like', "%$search%")
                      ->orWhere('kategori', 'like', "%$search%")
                      ->orWhere('harga', 'like', "%$search%");
            })
            ->paginate($per);

        return response()->json($data);
    }

    /**
     * Menyimpan menu baru.
     */
    public function store(Request $request)
    {
        // Validasi data yang diterima
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:255', // Menggunakan nama_menu
            'harga' => 'required|string|min:0',
            'kategori' => 'required|string|max:255',
            'status' => 'required|string|in:Tersedia,Habis',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi photo
        ]);

        // Simpan photo jika ada
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('menus', 'public'); // Simpan foto
        }

        // Simpan data menu ke database
        $menu = Menu::create($validated);

        // Kembalikan respons JSON
        return response()->json([
            'success' => true,
            'menu' => $menu,
        ]);
    }

    /**
     * Menampilkan detail menu berdasarkan ID.
     */
    public function show(Menu $menu)
    {
        return response()->json($menu);
    }

    /**
     * Memperbarui data menu.
     */
    public function update(Request $request, Menu $menu)
    {
        // Validasi data
        $validated = $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|string|min:0',
            'kategori' => 'required|string|max:255',
            'status' => 'required|string|in:Tersedia,Habis',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Cek apakah ada file baru
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($menu->photo && Storage::disk('public')->exists($menu->photo)) {
                Storage::disk('public')->delete($menu->photo);
            }

            // Simpan foto baru
            $validated['photo'] = $request->file('photo')->store('menus', 'public');
        }

        // Update data
        $menu->update($validated);

        return response()->json([
            'success' => true,
            'menu' => $menu,
        ]);
    }

    /**
     * Menghapus menu berdasarkan ID.
     */
    public function destroy(Menu $menu)
    {
        // Hapus photo jika ada
        if ($menu->photo) {
            Storage::disk('public')->delete($menu->photo);
        }

        // Hapus data menu
        $menu->delete();

        // Kembalikan respons JSON
        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus',
        ]);
    }
}
