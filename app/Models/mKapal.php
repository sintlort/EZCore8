<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mKapal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_kapal";

    protected $fillable = [
        'nama_kapal',
        'kapasitas',
        'deskripsi',
        'foto',
        'contact_service',
        'tanggal_beroperasi',
        'tipe_kapal',
    ];

    protected $dates = ['deleted_at'];

    public function KJadwal()
    {
        return $this->hasMany('App\Models\mDetailJadwal', 'id_kapal');
    }

    public function KGolongan()
    {
        return $this->hasMany('App\Models\mDetailGolongan','id_kapal');
    }
}
