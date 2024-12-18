<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    use HasFactory;

    // Specify the primary key if it differs from the default 'id'
    protected $table = 'workflow_steps';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'workflow_id',
        'prompt_id',
        'serial',
        'title',
    ];

    /**
     * Get the workflow that owns the step.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the prompt associated with the step.
     */
    public function prompt()
    {
        return $this->belongsTo(Prompt::class);
    }
}
