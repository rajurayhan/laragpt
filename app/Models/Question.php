<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    protected $table= 'questions';
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'serviceIds',
    ];

    protected $casts = [
        'serviceIds' => 'array',
    ];
}
