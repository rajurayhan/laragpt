<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateLog extends Model
{

    protected $fillable = ['date', 'deployed', 'next'];
    
    use HasFactory;
}
