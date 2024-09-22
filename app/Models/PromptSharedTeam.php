<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptSharedTeam extends Model
{
    use HasFactory;

    protected $table = 'prompt_shared_team';

    protected $fillable = [
        'promptId',
        'teamId',
    ];

    public function prompt()
    {
        return $this->belongsTo(Prompt::class,'promptId','id');
    }
    public function team()
    {
        return $this->belongsTo(Team::class,'teamId','id');
    }
}
