<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'prompt_id', 'user_id', 'message_content'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}