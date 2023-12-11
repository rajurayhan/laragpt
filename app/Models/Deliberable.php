<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliberable extends Model
{
    use HasFactory;

    protected $fillable = [
        'scopeOfWorkId',
        'deliverablesText',
    ];

    public function scopeOfWork()
    {
        return $this->belongsTo(ScopeOfWork::class, 'scopeOfWorkId', 'id');
    }
}
