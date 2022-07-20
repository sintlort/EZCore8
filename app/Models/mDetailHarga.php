<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mDetailHarga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_detail_harga";

    protected $fillable = [
        'id_harga',
        'id_detail_jadwal',
    ];

    protected $dates = ['deleted_at'];

    public function DHJadwal(){
        return $this->belongsTo('App\Models\mDetailJadwal','id_detail_jadwal');
    }

    public function DHHarga(){
        return $this->belongsTo('App\Models\mHarga','id_harga');
    }

}
