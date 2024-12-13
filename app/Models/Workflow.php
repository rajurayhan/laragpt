<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    // Specify the primary key if it differs from the default 'id'
    protected $table = 'workflows';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'title',
    ];

    /**
     * Get the workflow steps associated with the workflow.
     */
    public function steps()
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
