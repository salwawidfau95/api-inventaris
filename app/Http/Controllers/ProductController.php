<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Product;
use App\Models\Transactions;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() 
    {
        try {
            $data = Product::all();
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name'=>'required',
                'price'=>'required',
            ]);

            $prosesData = Product::create([
                'name' => $request->name,
                'price' => $request->price,
            ]);

            if ($prosesData) {
                return ApiFormatter::sendResponse(200, 'success', $prosesData);
            }else{
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal memproses tambah Data Product silahkan coba lagi.');
            }
            
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name'=>'required',
                'price'=>'required',
            ]);

            $checkProses = Product::where('id', $id)->update([
                'name' => $request->name,
                'price' => $request->price,
            ]);
            
            if ($checkProses) {
                $data = Product::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
            
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try{
            $checkProses = Product::where('id', $id)->delete();
            if ($checkProses) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data Product');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}