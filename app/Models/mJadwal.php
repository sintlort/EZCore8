<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mJadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_jadwal";

    protected $fillable = [
        'waktu',
        'id_dermaga',
        'jenis',
    ];

    protected $dates = ['deleted_at'];

    public function JDermaga()
    {
        return $this->belongsTo('App\Models\mDermaga', 'id_dermaga');
    }

    public function JDetailJadwalAsal()
    {
        return $this->hasMany('App\Models\mDetailJadwal','id_jadwal_asal');
    }

    public function JDetailJadwalTujuan()
    {
        return $this->hasMany('App\Models\mDetailJadwal','id_jadwal_tujuan');
    }
}
