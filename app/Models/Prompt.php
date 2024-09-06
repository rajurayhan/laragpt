<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = ['type','action_type', 'prompt', 'name','serial'];

    public function shared_user()
    {
        return $this->hasMany(PromptSharedUser::class);
    }
}
