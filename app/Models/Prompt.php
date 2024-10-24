<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = ['type','action_type', 'category_id', 'prompt', 'name','serial'];

    public function shared_user()
    {
        return $this->hasMany(PromptSharedUser::class);
    }
    public function shared_teams()
    {
        return $this->hasMany(PromptSharedTeam::class,'promptId');
    }

    public function categoryInfo()
    {
        return $this->belongsTo(PromptCategory::class, 'category_id', 'id');
    }
}
