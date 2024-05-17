<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingLink extends Model
{
    use HasFactory;
    protected $table = 'meeting_links';

    protected $primaryKey = 'id';

    protected $fillable = [
        'transcriptId',
        'meetingLink',
        'transcriptText',
        'serial',
    ];

    public function transcriptInfo()
    {
        return $this->belongsTo(MeetingTranscript::class, 'transcript_id');
    }
}
