<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Symfony\Component\Mime\Header\has;

class mGolongan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_golongan";

    protected $fillable = [
        'id_pelabuhan',
        'golongan',
        'keterangan',
        'harga',
    ];

    protected $dates = ['deleted_at'];

    public function GDetailGolongan()
    {
        return $this->hasMany('App\Models\mDetailGolongan', 'id_golongan');
    }

    public function GPelabuhan()
    {
        return $this->belongsTo('App\Models\mPelabuhan','id_pelabuhan');
    }
}
