<?php

namespace App\Console\Commands;

use App\Models\MeetingSummery;
use App\Models\ProblemsAndGoals;
use App\Models\ProjectOverview;
use App\Models\ProjectSummary;
use App\Services\Markdown2Html;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ConvertMdToHtmlContent extends Command
{
    protected $signature = 'convert:md2html';
    protected $description = 'Convert All Markdown content of Meeting Summery and Project SOW Module';

    public function handle()
    {

        // Meeting Summery
        $meetingSummeries = MeetingSummery::get(); 
        foreach($meetingSummeries as $summery){
            $summery->meetingSummeryText = Markdown2Html::convert($summery->meetingSummeryText);
            $summery->save();
        }

        // Project Summery
        $projectSummeries = ProjectSummary::get(); 
        foreach($projectSummeries as $summery){
            $summery->summaryText = Markdown2Html::convert($summery->summaryText);
            $summery->save();
        }

        // Problems and Goals
        $problems = ProblemsAndGoals::get(); 
        foreach($problems as $problem){
            $problem->problemGoalText = Markdown2Html::convert($problem->problemGoalText);
            $problem->save();
        }

        // Project Overview
        $overviews = ProjectOverview::get(); 
        foreach($overviews as $overview){
            $overview->overviewText = Markdown2Html::convert($overview->overviewText);
            $overview->save();
        }
        $this->info('Converted Successfully');
    }
}
