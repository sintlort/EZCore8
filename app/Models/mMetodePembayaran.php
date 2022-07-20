<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mMetodePembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_metode_pembayaran";

    protected $fillable = [
        'metode',
        'nama_metode',
        'deskripsi_metode',
        'nomor_rekening',
        'logo_metode',
        'payment_limit',
    ];

    protected $dates = ['deleted_at'];

    public function MPPembelian()
    {
        return $this->hasMany('App\Models\mPembelian','id_metode_pembayaran');
    }
}
