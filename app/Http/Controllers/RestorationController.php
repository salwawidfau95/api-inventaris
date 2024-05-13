<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Lending;
use App\Models\Restoration;
use App\Models\StuffStock;
use Illuminate\Http\Request;


class RestorationController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
    }

    public function store(Request $request, $lending_id) {
        try {
            $this->validate($request, [
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required', 
            ]);
            
            $lending = Lending::where('id', $lending_id)->first();
            //mendata jmlh masukan barang kembali yg bgs dan rusak
            $totalStuffRestoration = (int)$request->total_good_stuff + (int)$request->total_defec_stuff;
            //mengecek apakah jmlh barang dikembalikan > dr jmlh ketika meminjam
            if ((int)$totalStuffRestoration > (int)$lending['total_stuff']) { 
                return ApiFormatter::sendResponse(400, "bad request", 'Total barang kembali lebih banyak dari barang dipinjam!');
            }else {
                //updateOrCreate : klu udh ad data lending_id ny bakal diupdate datanya, klu belum dibikinin
                $restoration = Restoration::updateOrCreate([
                    'lending_id' => $request->lending_id
                ], [
                    'date_time' => $request->date_time,
                    'total_good_stuff' => $request->total_good_stuff, 
                    'total_defec_stuff' => $request->total_defec_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $stuffStock = StuffStock::where('stuff_id', $lending['stuff_id'])->first();
                //menghitung data baru yg akan dimasukkan ke stuffstock
                //totalAvailableStock penjumlahan sblmny dan barang bgs yg kembali 
                $totalAvailableStock = (int)$stuffStock['total_available'] + (int)$request->total_good_stuff;
                //totalDefecStock penjumlahan data defec sblmny dan defec yg kembali
                $totalDefecStock = (int)$stuffStock['total_defec'] + (int)$request->total_defec_stuff;
                $stuffStock->update([
                    'total_available' => $totalAvailableStock,
                    'total_defec' => $totalDefecStock,
                ]);
                //data yg akan dimunculkan, dr lending beserta relasi user, restoration, user milik restoration, barang
                $lendingRestoration = Lending::where('id', $lending_id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first(); 
                return ApiFormatter::sendResponse(200, "success", $lendingRestoration);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, "bad request", $err->getMessage());
        }
    }
}


