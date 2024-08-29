<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ConvertMdToHtmlContent extends Command
{
    protected $signature = 'convert:md2html';
    protected $description = 'Convert All Markdown content of Meeting Summery and Project SOW Module';

    public function handle()
    {


        $this->info('Converted Successfully');
    }
}
