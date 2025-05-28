<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PengirimanController extends Controller
{
    /**
     * Get paginated list of kurir
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        // $data = Kurir::select('kurir_id', 'name', 'email', 'phone', 'status', 'rating', 'photo')

        $data = Kurir::with(['user', 'menu'])->select('id', 'user_id', 'status') // Tambahkan relasi user
            ->when($request->search, function ($query, $search) {
                    $query->where('id', 'like', "%$search%")
                        // ->orWhere('', 'like', "%$search%")
                        ->orWhere('alamat_asal', 'like', "%$search%")
                        ->orWhere('alamat_tujuan', 'like', "%$search%");
            })->latest()->paginate($per);

        $no = ($data->currentPage()-1) * $per + 1;
        foreach($data as $item){
            $item->no = $no++;
        }

        return response()->json($data);
    }

    public function store(StoreKurirRequest $request)
    {
        $validatedData = $request->validated();

        // if ($request->hasFile('photo')) {
        //     if ($kurir->user->photo) {
        //         Storage::disk('public')->delete($kurir->user->photo);
        //     }
        //     $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        // }

        $kurir = Kurir::create($validatedData);
        $kurir->load('user'); // load relasi user

        return response()->json([
            'success' => true,
            'kurir' => [
                'id' => $kurir->id,
                'alamat_asal' => $kurir->alamat_asal,
                'alamat_tujuan' => $kurir->alamat_tujuan,
                'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                ],
                'menu' => [
                    'nama_menu' => $kurir->menu->nama_menu
                ],
            ],
        ]);
    }



    /**
     * Show a specific kurir
     */
    public function show(Kurir $kurir)
    {
        $kurir->load('user');

        return response()->json([
            // 'kurir'=> ['status' => $kurir->status],
            'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
                    'photo' => $kurir->user->photo,
                    // '' => $kurir->,
                    'alamat_asal' => $kurir->alamat_asal,
                    'alamat_tujuan' => $kurir->alamat_tujuan,
                    'nama_menu' => $kurir->menu->nama_menu
                ],
            ]);

    }

    /**
     * Update an existing kurir
     */
    public function update(UpdateKurirRequest $request, Kurir $kurir)
    {
        $validatedData = $request->validated();

        $updateUserData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // if ($request->hasFile('photo')) {
        //     if ($kurir->user->photo) {
        //         Storage::disk('public')->delete($kurir->user->photo);
        //     }

        //     $updateUserData['photo'] = $request->file('photo')->store('photo', 'public');
        // }

        $kurir->user->update($updateUserData);
        $kurir->update($validatedData);

        return response()->json([
            'success' => true,
            // 'kurir' => $kurir->load('user')
            'kurir' => [
                'id' => $kurir->id,
                // '' => $kurir->,
            ]
        ]);
    }


    /**
     * Get all kurir
     */
    public function get()
    {
        return response()->json([
            'success' => true,
            'data' => Kurir::all(),
            // 'data' => Kurir::select('id', 'name', 'email', 'phone', 'status', 'rating', 'photo')->get()
        ]);
    }

    public function destroy(Kurir $kurir)
    {

        // Hapus data user yang terkait
        if ($kurir->user) {
            $kurir->user->delete();
        }

        // Hapus data kurir
        $kurir->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data kurir berhasil dihapus'
        ]);
    }
}
