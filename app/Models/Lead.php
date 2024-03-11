<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['firstName', 'lastName', 'company', 'phone', 'email', 'description', 'projectTypeId'];

    public function projectType()
    {
        return $this->belongsTo(ProjectType::class, 'projectTypeId');
    }
}

