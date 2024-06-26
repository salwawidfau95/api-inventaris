<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Transactions;
use App\Models\Product;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index() 
    {
        try {
            $data = Transactions::all();
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'product_id'=>'required',
                'order_date'=>'required',
                'quantity'=>'required',
            ]);

            $prosesData = Transactions::create([
                'product_id' => $request->product_id,
                'order_date' => $request->order_date,
                'quantity' => $request->quantity,
            ]);

            if ($prosesData) {
                return ApiFormatter::sendResponse(200, 'success', $prosesData);
            }else{
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal memproses tambah Data Transactions silahkan coba lagi.');
            }
            
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'product_id'=>'required',
                'order_date'=>'required',
                'quantity'=>'required',
            ]);

            $checkProses = Transactions::where('id', $id)->update([
                'product_id' => $request->product_id,
                'order_date' => $request->order_date,
                'quantity' => $request->quantity,
            ]);
            
            if ($checkProses) {
                $data = Transactions::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try{
            $checkProses = Transactions::where('id', $id)->delete();
            if ($checkProses) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data Transactions');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}