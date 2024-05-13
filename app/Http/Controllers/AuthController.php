<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\ApiFormatter;

class AuthController extends Controller
{
    public function __construct() //__construct di oop : bakal dijalanin walau gadipanggil 
    {
        //middleware : membatasi, nama2 function yg hanya bisa diakses setelah login 
        $this->middleware('auth:api', ['except' => ['login']]); //except : fungsi mn yg blh diakses sebelum suatu tindakan (login)
    }

    public function login(Request $request)
    {
	    $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) // ! buat nampilin kalau gacocok //package jwt yg diinstall buat token 
        {
            return ApiFormatter::sendResponse(400, 'User not found', 'Silahkan cek kembali email & password anda!!!');
        }

        $respondWithToken = [
            'access_token' => $token, //token yg simpen di session
            'token_type' => 'bearer', //jenis token
            'user' => auth()->user(), //dta info yg login dr username/pw
            'expires_in'=> auth()->factory()->getTTL() * 60 * 24 //untuk menentukan waktu simpan loginnya
        ];

        return ApiFormatter::sendResponse(200, 'Logged-in', $respondWithToken);
    }

    public function me()
    {
        return ApiFormatter::sendResponse(200, 'success', auth()->user()); //mangambil prfil yg login
    }

    public function logout()
    {
        auth()->logout(); //menghapus profil untuk logout

        return ApiFormatter::sendResponse(200, 'Logged-in', 'Berhasil logout!!!');
    }
}