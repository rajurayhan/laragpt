<?php

    namespace App\Services;

    use App\Enums\PromptType;
use App\Models\Prompt;

    class PromptService
    {
        public static function findPromptByType(PromptType $type){
            $prompt = Prompt::where('type', $type)->first();

            if($prompt){
                return $prompt;
            }
            return null;
        }
    }
