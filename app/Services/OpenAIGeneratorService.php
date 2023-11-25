<?php

    namespace App\Services;
    use OpenAI\Laravel\Facades\OpenAI;

    class OpenAIGeneratorService
    {
        private $model = 'gpt-4-1106-preview';

        public static function generateSummery($transcript){
            // Step One: Generate Summery from transcript
            $summeryResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => 'This is a transcript of a sales call with a potential new client for our other company, Defense Acquisition Solutions Group. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps," which should be in bullet point format. Be sure to identify which person from the "Call Participants" is responsible for each "Next Steps" item and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => $transcript],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);
            $summery = $summeryResult['choices'][0]['message']['content'];

            return $summery;
        }

        public static function generateProblemsAndGoals($transcript){
            $problemsAndGoalsResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => "Help me turn the following transcript from a virtual meeting I had with a potential client into bullet points that address the client's problems and goals. The potential client is looking for help from our company, to help solve their problems and accomplish their goals with the services we offer."],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => $transcript],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $problemsAndGoals = $problemsAndGoalsResult['choices'][0]['message']['content'];

            return $problemsAndGoals;
        }

        public static function generateProjectOverview($problemsAndGoals){
            $projectOverViewResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => "Help me take the following points that were put together from a conversation I had with a potential client asking for our businesses to help and turn them into paragraphs. I need this text to be a very easy-to-read, well-written, and detailed project description/overview. Put into paragraph format only that is easy to read."],

                    ['role' => 'system', 'content' => 'I am sending you markdown and you will always return output in markdown format with proper line braeks with a heading Project Overview.'],
                    ['role' => 'user', 'content' => $problemsAndGoals],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $projectOverView = $projectOverViewResult['choices'][0]['message']['content'];

            return $projectOverView;
        }

        public static function generateScopeOfWork($problemsAndGoals){
        }

        public static function generateDeliverables($scopeOfWork){
        }


        public static function prepareTranscript($transcript){
            return preg_replace('/\d{2}:\d{2}\s/', '', $transcript);
        }
    }
