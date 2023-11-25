<?php

    namespace App\Services;

    class OpenAIGeneratorService 
    {
        private $model = 'gpt-4-1106-preview';

        public static function generateSummery($transcript){
        }

        public static function generateProblemsAndGoals($transcript){
        }

        public static function generateProjectOverview($problemsAndGoals){
        }

        public static function generateScopeOfWork($problemsAndGoals){
        }

        public static function generateDeliverables($scopeOfWork){
        }


        public static function prepareTranscript($transcript){
            return preg_replace('/\d{2}:\d{2}\s/', '', $transcript);
        }
    }