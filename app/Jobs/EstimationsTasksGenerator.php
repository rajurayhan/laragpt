<?php

namespace App\Jobs;

use App\Models\Deliberable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

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
        DB::table('queue_data')->insert([
            'title'=> $this->deliberable->title,
        ]);
        sleep(1);
    }
}
