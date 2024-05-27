<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverablesNotes extends Model
{
    protected $table = 'deliverables_notes';
    use HasFactory;

    protected $fillable = [
        'transcriptId',
        'problemGoalId',
        'note',
        'noteLink',
    ];
}
