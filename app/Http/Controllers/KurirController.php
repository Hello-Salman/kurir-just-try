<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Http\Requests\StoreKurirRequest;
use App\Http\Requests\UpdateKurirRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class KurirController extends Controller
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

        $data = Kurir::with('user')->select('id', 'user_id', 'status') // Tambahkan relasi user
            ->when($request->search, function ($query, $search) {
                    $query->where('id', 'like', "%$search%")
                        // ->orWhere('', 'like', "%$search%")
                        ->orWhere('status', 'like', "%$search%");
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
                'status' => $kurir->status,
                // '' => $kurir->,
                'user' => [
                    'name' => $kurir->user->name,
                    'email' => $kurir->user->email,
                    'phone' => $kurir->user->phone,
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
                    'status' => $kurir->status,
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

        if ($request->filled('password')) {
            $updateUserData['password'] = Hash::make($request->password);
        }

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
                'status' => $kurir->status,
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
            'data' => Kurir::with("user")->get(),
            // 'data' => Kurir::select('id', 'name', 'email', 'phone', 'status', 'rating', 'photo')->get()
        ]);
    }

    public function destroy(Kurir $kurir)
    {
        // Hapus foto dari storage jika user memiliki foto
        if ($kurir->user && $kurir->user->photo) {
            Storage::disk('public')->delete($kurir->user->photo);
        }

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
