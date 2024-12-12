<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocketUserActivity extends Model
{
    protected $table= 'socket_user_activities';
    use HasFactory;

    protected $fillable = ['user_id', 'activity_type','document_id','document_related_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
