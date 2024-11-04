<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bookmark extends Model
{
    protected $table= 'bookmarks';
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'conversationId',
        'conversationDetailId',
        'user_id',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
