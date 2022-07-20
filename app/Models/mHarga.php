<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mHarga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_harga";

    protected $fillable = [
        'harga',
        'id_pelabuhan_asal',
        'id_pelabuhan_tujuan',
        'id_golongan',
    ];

    protected $dates = ['deleted_at'];

    public function HPelabuhanAsal(){
        return $this->belongsTo('App\Models\mPelabuhan','id_pelabuhan_asal');
    }

    public function HPelabuhanTujuan(){
        return $this->belongsTo('App\Models\mPelabuhan','id_pelabuhan_tujuan');
    }

    public function HDetailGolongan(){
        return $this->belongsTo('App\Models\mDetailGolongan','id_golongan');
    }
}
