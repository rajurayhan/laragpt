<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGptThreadUsing extends Model
{
    protected $table= 'chat_gpt_thread_using';
    use HasFactory;
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assistantId',
        'threadId',
        'user_id',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function userInfo()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
