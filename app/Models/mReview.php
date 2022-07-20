<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mReview extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "tb_review";

    protected $fillable = [
        'id_user',
        'id_pembelian',
        'review',
        'score',
    ];

    protected $dates = ['deleted_at'];

    public function RUser()
    {
        return $this->belongsTo('App\Models\mUser', 'id_user');
    }

    public function RPembelian()
    {
        return $this->belongsTo('App\Models\mPembelian','id_pembelian');
    }
}
