<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mDetailJadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_detail_jadwal";

    protected $fillable = [
        'id_jadwal_asal',
        'id_jadwal_tujuan',
        'estimasi_waktu',
        'id_kapal',
        'tanggal',
    ];

    protected $dates = ['deleted_at'];

    public function DJJadwalAsal()
    {
        return $this->belongsTo('App\Models\mJadwal', 'id_jadwal_asal');
    }

    public function DJJadwalTujuan()
    {
        return $this->belongsTo('App\Models\mJadwal', 'id_jadwal_tujuan');
    }

    public function DJDetailHarga()
    {
        return $this->hasMany('App\Models\mDetailHarga', 'id_detail_jadwal');
    }

    public function DJKapal()
    {
        return $this->belongsTo('App\Models\mKapal','id_kapal');
    }
}
