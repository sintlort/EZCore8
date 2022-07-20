<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mHakKapal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_hak_akses_kapal";

    protected $fillable = [
        'id_kapal',
        'id_user'
    ];
}
