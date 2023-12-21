<?php

namespace App\Services;
use League\CommonMark\CommonMarkConverter;

class Markdown2Html
{
    public static function convert(string $text){
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convert($text);
    }
}
