<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api');
    }
    
    public function index() 
    {
        try {
            $data = User::all()->toArray();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email|unique:users',
                'username' => 'required|min:4|unique:users',
                'role' => 'required',
                'password' => 'required',
            ]);
            
            $prosesData = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'role' => $request->role,
                'password' => Crypt::encrypt($request->password),
            ]);

            if ($prosesData) {
                return ApiFormatter::sendResponse(200, 'success', $prosesData);
            }else{
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal memproses tambah data stuff silahkan coba lagi.');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = User::where('id', $id)->first();
            
            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'email' => 'required|unique:users,email,' .$id,
                'username' => 'required|min:4|unique:users,username,' .$id,
                'role' => 'required',
                'password' => 'required',
            ]);
            
            $checkProses = User::where2('id', $id)->update([
                'email' => $request->email,
                'username' => $request->username,
                'role' => $request->role,
                'password' => Crypt::encrypt($request->password),
            ]);

            if ($checkProses) {
                $data = User::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try{
            $checkProses = User::where('id', $id)->delete();
            if ($checkProses) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data stuff');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = User::onlyTrashed()->get();
            return ApiFormatter::sendResponse(200, 'success', $data);
        }  catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkRestore = User::onlyTrashed()->where('id', $id)->restore();
            if ($checkRestore) {
                $data = User::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function permanenDelete($id)
    {
        try {
            $checkPermanenDelete = User::onlyTrashed()->where('id', $id)->forceDelete();
            if ($checkPermanenDelete) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanent data stuff!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}
