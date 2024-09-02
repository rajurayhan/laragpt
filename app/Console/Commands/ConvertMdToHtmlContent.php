<?php

namespace App\Console\Commands;

use App\Models\MeetingSummery;
use App\Services\Markdown2Html;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ConvertMdToHtmlContent extends Command
{
    protected $signature = 'convert:md2html';
    protected $description = 'Convert All Markdown content of Meeting Summery and Project SOW Module';

    public function handle()
    {

        $meetingSummeries = MeetingSummery::get(); 
        foreach($meetingSummeries as $summery){
            \Log::info(Markdown2Html::convert($summery->meetingSummeryText));
            $summery->meetingSummeryText = Markdown2Html::convert($summery->meetingSummeryText);
            $summery->save();
        }
        $this->info('Converted Successfully');
    }
}
