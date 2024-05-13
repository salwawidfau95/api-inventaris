<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StuffStock;
use App\Models\Lending;
use App\Helpers\ApiFormatter;

class LendingController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'stuff_id' => 'required', //disamain sama payload postmannya 
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required',
            ]);
            // user_id tdk masuk ke validasi karena value ny bkn bersumber dr luar (dipilih user)

            //cek total_available stuff terkait
            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)->value('total_available');

            if (is_null($totalAvailable)) {
                return ApiFormatter::sendResponse(400, "bad request", 'Belum ada data inbound!');
            }elseif ((int)$request->total_stuff > (int)$totalAvailable) { //int : untuk mengubah & memastikan type datanya integer
                return ApiFormatter::sendResponse(400, "bad request", 'Stok tidak tersedia');
            }else {
                $lending = Lending::create([
                    'stuff_id' => $request->stuff_id,//disamain dari validate //dari kolom database & model 'stuff_id'
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'notes' => $request->notes ? $request->notes : '-',
                    'total_stuff' => $request->total_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
                $stuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);

                $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.stuffStock')-> first(); //mengambil data dari table lain tulisan didalem with disamain dari nama function relasi di model
                //klu yg pake titik ambil data yg dari relasi kerelasiin lg, ambil function yg ad di dalam stuff yg udh direlasiiin ke stuffStock
                
                return ApiFormatter::sendResponse(200, "success", $dataLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, "bad request", $err->getMessage());
        }
    }

    public function index() 
    {
        try {
            // with : menyertakan data dari relasi, isi di with disamakan dgn nama function relasi di model :: nya
            $data = Lending::with('stuff', 'user', 'restoration')->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        } 
    }

    public function destroy($id)
{
    try {
        $lending = Lending::findOrFail($id);

        // Periksa apakah peminjaman memiliki proses pengembalian
        if ($lending->restoration()->exists()) {
            return ApiFormatter::sendResponse(400, 'bad request', 'Peminjaman tidak dapat dibatalkan karena sudah ada proses pengembalian');
        }

        // Ambil jumlah barang yang dipinjam
        $total_stuff = $lending->total_stuff;

        // Kembalikan jumlah barang yang dipinjam ke total_available pada stuff_stocks
        $stuff_stock = StuffStock::where('stuff_id', $lending->stuff_id)->first();
        $stuff_stock->total_available += $total_stuff;
        $stuff_stock->save();

        $lending->delete();

        return ApiFormatter::sendResponse(200, 'success', 'Peminjaman berhasil dibatalkan');
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}

    public function show($id){
        try {
            $data = Lending::where('id', $id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first(); 
                return ApiFormatter::sendResponse(200, "success", $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, "bad request", $err->getMessage());
        }
    }

}
