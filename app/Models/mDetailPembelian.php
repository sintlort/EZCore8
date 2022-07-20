<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mDetailPembelian extends Model
{
    use HasFactory;

    protected $table = "tb_detail_pembelian";

    protected $primaryKey = "id_detail_pembelian";

    protected $fillable = [
        'id_pembelian',
        'id_card',
        'kode_tiket',
        'nama_pemegang_tiket',
        'no_id_card',
        'harga',
        'status'
    ];

    protected $dates = ['deleted_at'];

    public function DPPembelian()
    {
        return $this->belongsTo('App\Models\mPembelian','id_pembelian');
    }

    public function DPCard()
    {
        return $this->belongsTo('App\Models\mCard','id_card');
    }


}
