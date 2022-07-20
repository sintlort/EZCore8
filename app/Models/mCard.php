<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_card";

    protected $fillable = [
        'card'
    ];

    protected $dates = ['deleted_at'];

    public function CDPembelian ()
    {
        return $this->hasMany('App\Models\mDetailPembelian','id_card');
    }

}
