<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id', 'assistantId', 'threadId'];

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
