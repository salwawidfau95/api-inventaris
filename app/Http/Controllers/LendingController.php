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
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required',
            ]);
            // user_id tdk masuk ke validasi karena value ny bkn bersumber dr luar (dipilih user)

            //cek total_available stuff terkait
            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)-> value('total_available');

            if (is_null($totalAvailable)) {
                return ApiFormatter::sendResponse(400, "bad request", 'Belum ada data inbound!');
            }elseif ((int)$request->total_stuff > (int)$totalAvailable) {
                return ApiFormatter::sendResponse(400, "bad request", 'Stok tidak tersedia');
            }else {
                $lending = Lending::create([
                    'stuff_id'=> $request->stuff_id,
                    'date_time'=> $request->date_time,
                    'name'=> $request->name,
                    'notes'=> $request->notes ? $request->notes : '-',
                    'total_stuff'=> $request->total_stuff,
                    'user_id'=> auth()->user()->id,
                ]);

                $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
                $stuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);

                $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.stuffStock')-> first();
                
                return ApiFormatter::sendResponse(200, "success", $dataLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, "bad request", $err->getMessage());
        }
    }
}
