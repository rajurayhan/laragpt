<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptSharedUser extends Model
{
    use HasFactory;

    protected $fillabel = ['user_id', 'prompt'];

    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }
}
