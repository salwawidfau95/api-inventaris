<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }
    
    public function index() 
    {
        try {
            //ambil data yang mau di tambahkan
            $data = Stuff::all()->toArray();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            //$request akan mengambil data dari inputan 
            // validasi
            // 'nama_column' => 'validasi'
            $this->validate($request, [
                'name'=>'required',
                'category'=>'required',
                //required : wajib diisi
            ]);

            $prosesData = Stuff::create([
                'name' => $request->name,
                'category' => $request->category,
            ]);

            if ($prosesData) {
                return ApiFormatter::sendResponse(200, 'success', $prosesData);
            }else{
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal memproses tambah data stuff silahkan coba lagi.');
                //akan dijalankan jika catch tdk bisa mendetek error ttg proses data
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    //$id : dari rpute yg ad {}
    public function show($id)
    {
        try {
            $data = Stuff::where('id', $id)->first();
            // first() : klu gd, ttp success data kosong
            // firstOrFail() : klu gd, munculnya error
            // find() : pengganti where hasilnya sama kyk yg first : Stuff::find($id)
            
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name'=>'required',
                'category'=>'required',
            ]);

            $checkProses = Stuff::where('id', $id)->update([
                'name' => $request->name,
                'category' => $request->category,
            ]);

            if ($checkProses) {
                // ::create([]) : menghasilkan data yg ditambah 
                // ::update([]) : menghasilkan boolean, jd buat ambil data tebaru dicari lg

                $data = Stuff::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
{
    try {
        $checkProses = Stuff::with('inboundStuffs', 'stuffStock', 'lendings')->find($id);

        if ($checkProses->inboundStuffs()) {
            return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data inbound");
        } 
        elseif ($checkProses->stuffStock()) {
            return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data stuff stock");
        }
        elseif($checkProses->lendings()) {
            return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data lending");
        }
        else {
            $checkProses->delete();
            return ApiFormatter::sendResponse(200, true, "berhasil hapus data barang dengan id $id", ['id' => $id]);
        }

    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}

    public function trash()
    {
        try {
            //onlyTrashed() : memanggil data sampah/yg sdh di hps/ deleted_at ny terisi
            $data = Stuff::onlyTrashed()->get();
            return ApiFormatter::sendResponse(200, 'success', $data);
        }  catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            //restore : mengembalikan data yg dihps/menghapus deleted_at ny
            $checkRestore = Stuff::onlyTrashed()->where('id', $id)->restore();
            if ($checkRestore) {
                $data = Stuff::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function permanenDelete($id)
    {
        try {
            //forceDelete() : menghapus permanent (hilang jg data di db ny)
            $checkPermanenDelete = Stuff::onlyTrashed()->where('id', $id)->forceDelete();
            if ($checkPermanenDelete) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanent data stuff!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}