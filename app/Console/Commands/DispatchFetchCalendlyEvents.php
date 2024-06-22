<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchCalendlyEventsJob;

class DispatchFetchCalendlyEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:fetch-calendly-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the FetchCalendlyEventsJob';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        FetchCalendlyEventsJob::dispatch();
        $this->info('FetchCalendlyEventsJob dispatched.');
        return 0;
    }
}
