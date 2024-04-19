<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    //jika di migrationnya menggunakan $table->softdeletes
    use SoftDeletes;

    // yg harus ada fillable atau guarded (wajib diisi dari dalam atau otomatis)
    // protected $guarded = ['id'];
    protected $fillable = ["name", "category"]; //untuk menentuka column yg wajib diisi oleh user/dari luar

    //relasi
    //nama function : samain kyk model, kata pertama huruf kecil
    //model yg PK : hasOne / hasMany
    //panggil namaModelRelasi:class

    public function stuffStock(){
        return $this->hasOne(StuffStock::class);
    }

    //klu relasinya hasMany nama funcny jamak pake s 
    public function inboundStuffs(){
        return $this->hasMany(InboundStuff::class);
    }

    public function lendings(){
        return $this->hasMany(Lending::class);
    }
}
