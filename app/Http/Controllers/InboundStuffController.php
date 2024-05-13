<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\ApiFormatter;
use App\Models\InboundStuff;
use App\Models\Stuff;
use App\Models\StuffStock;

class InboundStuffController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api');
    }

    public function index(Request $request) 
    {
        try {
            if ($request->filter_id) {
                $data = InboundStuff::where('stuff_id', $request->filter_id)->with('stuff', 'stuff.stuffStock')->get();
            }else{
                $data = InboundStuff::all();
            }
//             `if-else` digunakan untuk pengecekan, apakah inb$inboundData menginginkan data `inbound_stuffs` secara umum/keseluruhan ataukah inb$inboundData menginginkan data `inbound_stuffs` yang spesifik berdasarkan `stuff` yang ia inginkan. filter_id diambil dari nama key pada query params nya

// relasi `stuff.stuffStock` berfungsi untuk mengambil relasi antara `stuff` dengan `stuff_stocks` dari hasil relasi `stuff` dengan `inbound_stuffs`

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proof_file' => 'required|image', //proof_file : type file image (jpg, jpeg, svg, png, webp)
            ]);
            
            //$request->file() : ambil data yg type nya file
            //getClientOriginalName() : ambil nama asli dari file yg di upload
            //Str::random(jumlah_karakter) : generate random karakter sebanyak jumlah
            $nameImage = Str::random(5) . "_" . $request->file('proof_file')->getClientOriginalName();
            // move() : memindahkan file yg di upload ke folder public, dan nama file nya mau apa
            $request->file('proof_file')->move('upload-images', $nameImage);
            $pathImage = url('upload-images/'. $nameImage);

            $inboundData = InboundStuff::create([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                //yg dimasukkan ke db data lokasi url gmbrny
                'proof_file' => $pathImage,
            ]);

            if ($inboundData) {
                $stockData = StuffStock::where('stuff_id', $request->stuff_id)->first();
                if ($stockData) { //klu ad stuffstock yg stuff_id ny kyk yg di buat ada
                    $total_available = (int)$stockData['total_available'] + (int)$request->total; //(int) : memastikan klu dia int, klu bkn diubah jd int
                    $stockData->update(['total_available' => $total_available]);
                }else { //klu stock ny blm ad, dibuat
                    StuffStock::create([
                        'stuff_id' => $request->stuff_id,
                        'total_available' => $request->total, //ttl_available ny dr inputan total inbound
                        'total_defec'=> 0,
                    ]);
                }
                //ambil dta mulai dr stuff, inboundStuff, dan stuffStock dr stuff_id terkait
                $stuffWithInboundAndStock = Stuff::where('id', $request->stuff_id)->with('inboundStuffs', 'stuffStock')->first();
                return ApiFormatter::sendResponse(200, 'success', $stuffWithInboundAndStock);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

public function destroy($id)
    {
        try {
            $inboundData = InboundStuff::where('id', $id)->with('stuff.stuffStock')->first();
            // simpan data dri inbound yang diperlukan / akan digunakan nnti setelah delete
            $stuffId = $inboundData['stuff_id'];
            $totalInbound = $inboundData['total'];
            
            // kurangi total_available sblumnya dengan total de awal yang akan dihapus
            $dataStock = StuffStock::where('stuff_id', $inboundData['stuff_id'])->first();
            $total_available = (int) $dataStock['total_available'] - (int)$totalInbound;
            
            $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
            
            if ((int)$minusTotalStock > (int)$inboundData['total_available']) { 
                return ApiFormatter::sendResponse (400, "bad request", 'Jumlah total inbound yang akan dihapus lebih besar dari total available stuff saat ini!');
            }else{
                $inboundData->delete();
                $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
                if ($minusTotalStock){
                    $updatedStuffWithInboundAndStock = Stuff::where('id', $inboundData['stuff_id'])->with('inboundStuffs','stuffStock')->first();
                    
                    $inboundData->delete();
                    return ApiFormatter::sendResponse(200,'Success',$updatedStuffWithInboundAndStock);
                }
            }
            // delete ibound terakhir agar data stuff_id di inbound bisa digunakan untuk mengammbil data terbaru
            // $inboundData->delete();
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, "bad request", $err->getMessage());
        }
    }
    
    public function trash()
    {
        try {
            $datatrash = InboundStuff::onlyTrashed()->get();
            return ApiFormatter::sendResponse(200, 'success', $datatrash);
        }  catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    
    public function restore($id)
    {
        try {
            $restoredRows = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($restoredRows > 0) {
                $inboundData = InboundStuff::find($id);
                $stuffId = $inboundData['stuff_id'];
                $totalInbound = $inboundData['total'];
                $inboundData->delete();
    
                // Tambahkan total_available untuk mengembalikan total seperti sebelumnya
                $dataStock = StuffStock::where('stuff_id', $stuffId)->first();
                $total_available = (int) $dataStock['total_available'] += (int) $totalInbound;
                $plusTotalStock = $dataStock->update(['total_available' => $total_available]);
    
                if ($plusTotalStock) {
                    $updatedStuff = Stuff::where('id', $stuffId)
                        ->with('inboundStuffs', 'stuffStock')
                        ->first();
    
                    return ApiFormatter::sendResponse(200, "success", $updatedStuff);
                }
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, "bad request", $err->getMessage());
        }
    }
    
    public function permanenDelete(InboundStuff $inboundStuff, Request $request, $id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proof/'.$getInbound->proof_file));
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
    
            return Apiformater::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }   
    
    // private function deleteAssociatedFile(InboundStuff $inboundStuff)
    // {
    //     $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proof';

    //      $filePath = public_path('proof/'.$inboundStuff->proof_file);
    
    //     if (file_exists($filePath)) {
    //         unlink(base_path($filePath));
    //     }
    // }
    
}

