<?php

    namespace App\Services;
    use OpenAI\Laravel\Facades\OpenAI;

    class OpenAIGeneratorService
    {
        private $model = 'gpt-4-1106-preview';

        public static function generateSummery($transcript, $promptText){
            // Step One: Generate Summery from transcript
            $summeryResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => 'This is a transcript of a sales call with a potential new client for our other company, Defense Acquisition Solutions Group. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps," which should be in bullet point format. Be sure to identify which person from the "Call Participants" is responsible for each "Next Steps" item and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => self::prepareTranscript($transcript)],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);
            $summery = $summeryResult['choices'][0]['message']['content'];
            \Log::info(['summery' => $summery]);

            return $summery;
        }
        public static function generateMeetingSummery($transcript, $promptText){
            // Step One: Generate Summery from transcript
            $summeryResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => 'This is a transcript of a meeting that I had with a client. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call/Meeting Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps/Tasks," which should be in bullet point format. Be sure to identify which person from the "Call/Meeting Participants" is responsible for each "Next Steps/Tasks" item, break each item into individual bullet points and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => self::prepareTranscript($transcript)],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);
            $summery = $summeryResult['choices'][0]['message']['content'];
            \Log::info(['summery' => $summery]);

            return $summery;
        }

        public static function generateProblemsAndGoals($transcript, $promptText){
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
            \Log::info(['problemsAndGoals' => $problemsAndGoals]);

            return $problemsAndGoals;
        }

        public static function generateProjectOverview($problemsAndGoals, $promptText){
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
            \Log::info(['projectOverView' => $projectOverView]);

            return $projectOverView;
        }

        public static function generateScopeOfWork($problemsAndGoals, $promptText){
            $scopeOfWorkResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => "I need your help in creating a very detailed bullet list for a scope of work based on the following Problems & Goals bullet list I created before. I need your help in making sure the new scope of work list you will be creating is very detailed and expanded upon as much as you can so we make sure nothing is missed for the project scope. I will end up using what you come up with in a proposal for a potential client who reached out to our company, asking us for help in the form of the services we offer. Be sure to add in quality control and testing items if not already mentioned in the list that I am providing you with. Please feel free to add the additional scope of work points that you think are missing and need to be added based on the main service the client is asking for our help with."],

                    ['role' => 'system', 'content' => 'I am sending you markdown and you will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => $problemsAndGoals],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $scopeOfWork = $scopeOfWorkResult['choices'][0]['message']['content'];
            \Log::info(['scopeOfWork' => $scopeOfWork]);

            return $scopeOfWork;
        }

        public static function generateDeliverables($scopeOfWork, $promptText){

            $deliverablesResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => "Help me take the following SOW list for a new project and turn them into matching \"Deliverables\" items. Remember that for every individual SOW list item, make sure you have a deliverable that matches. The formatting for this list should be in bullet point format. Then, take the same list you just created and provide me an estimate of how many hours and a timeline it would take to complete each of the deliverable items."],

                    ['role' => 'system', 'content' => 'I am sending you markdown and you will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => $scopeOfWork],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $deliverables = $deliverablesResult['choices'][0]['message']['content'];
            \Log::info(['deliverables' => $deliverables]);
            return $deliverables;
        }


        public static function prepareTranscript($transcript){
            return preg_replace('/\d{2}:\d{2}\s/', '', $transcript);
        }
    }
