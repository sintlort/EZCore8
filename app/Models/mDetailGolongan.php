<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mDetailGolongan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_detail_golongan";

    protected $fillable = [
        'id_golongan',
        'id_kapal',
        'jumlah',
    ];

    protected $dates = ['deleted_at'];

    public function DGGolongan()
    {
        return $this->belongsTo('App\Models\mGolongan','id_golongan');
    }

    public function DGKapal()
    {
        return $this->belongsTo('App\Models\mKapal','id_kapal');
    }
}
