<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Symfony\Component\Mime\Header\has;

class mPelabuhan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_pelabuhan";

    protected $fillable = [
        'kode_pelabuhan',
        'nama_pelabuhan',
        'lokasi_pelabuhan',
        'alamat_kantor',
        'foto',
        'latitude',
        'longtitude',
        'lama_beroperasi',
        'status',
        'tipe_pelabuhan'
    ];

    protected $dates = ['deleted_at'];

    public function PDermaga()
    {
        return $this->hasMany('App\Models\mDermaga', 'id_pelabuhan');
    }

    public function PJadwalAsal()
    {
        return $this->hasMany('App\Models\mJadwal','id_asal_pelabuhan');
    }

    public function PJadwalTujuan()
    {
        return $this->hasMany('App\Models\mJadwal','id_tujuan_pelabuhan');
    }
}
