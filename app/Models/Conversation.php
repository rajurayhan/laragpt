<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id', 'assistantId', 'threadId','workflow_id','running_step'];

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shared_user()
    {
        return $this->hasMany(ConversationSharedUser::class);
    }
}
