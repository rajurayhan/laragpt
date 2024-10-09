<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Memory extends Model
{
    protected $table= 'memories';
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'prompt',
        'promptIds',
    ];

    protected $casts = [
        'promptIds' => 'array',
    ];
}
