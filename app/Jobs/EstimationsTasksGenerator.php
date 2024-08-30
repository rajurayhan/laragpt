<?php

namespace App\Jobs;

use App\Http\Controllers\Api\EstimationsTasksController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EstimationsTasksGenerator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deliberable;

    public function __construct($deliberable)
    {
        $this->deliberable = $deliberable;
    }

    /**
     * Execute the job.
     */
    public function handle(){
        $EstimationsTasksController = new EstimationsTasksController();
        $EstimationsTasksController->create(new Request([
            'problemGoalId'=> $this->deliberable->problemGoalId,
            'deliverableId'=> $this->deliberable->id,
        ]));
    }
}
