<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mDermaga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_dermaga";

    protected $fillable = [
        'nama_dermaga',
        'id_pelabuhan',
    ];

    protected $dates = ['deleted_at'];

    public function DPelabuhan()
    {
        return $this->belongsTo('App\Models\mPelabuhan', 'id_pelabuhan');
    }

    public function DJadwal()
    {
        return $this->hasMany('App\Models\mJadwal','id_dermaga');
    }

}
