<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswer extends Model
{
    protected $table= 'question_answers';
    use HasFactory;
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'answer',
        'title',
        'questionId',
        'transcriptId',
        'problemGoalId',
    ];

    public function questionInfo()
    {
        return $this->belongsTo(Question::class, 'questionId', 'id');
    }
}
