<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class mUserNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tb_user_notification";

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'notification_by',
        'status',
        'type',
        'click_action'
    ];

    protected $dates = ['deleted_at'];

    public function UNUser()
    {
        return $this->belongsTo(mUser::class,'user_id');
    }

}
