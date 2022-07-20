<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class mUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tb_user';

    protected $fillable = [
        'nama',
        'alamat',
        'jeniskelamin',
        'nohp',
        'email',
        'password',
        'chat_id',
        'pin',
        'kode_verifikasi_email',
        'foto',
        'role',
        'fcm_token',
        'token_login',
        'verified_at',
    ];

    protected $hidden = [
        'password',
    ];

    public function UReview()
    {
        return $this->hasMany('App\Models\mReview', 'id_user');
    }

    public function UPembelian()
    {
        return $this->hasMany('App\Models\mPembelian','id_user');
    }
}
