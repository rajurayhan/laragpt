<?php

    namespace App\Services;
    use OpenAI\Laravel\Facades\OpenAI;
    use Illuminate\Support\Facades\Log;

    class OpenAIGeneratorService
    {
        private $model = 'gpt-4-1106-preview';

        public static function generateSummery($transcript, $promptText){
            // Step One: Generate Summery from transcript
            $summeryResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $promptText],
                    // ['role' => 'system', 'content' => 'This is a transcript of a sales call with a potential new client for our other company, Defense Acquisition Solutions Group. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps," which should be in bullet point format. Be sure to identify which person from the "Call Participants" is responsible for each "Next Steps" item and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => self::prepareTranscript($transcript)],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);
            $summery = $summeryResult['choices'][0]['message']['content'];
            Log::info(['summery' => $summery]);

            return $summery;
        }
        public static function generateMeetingSummery($transcript, $promptText){
            // Step One: Generate Summery from transcript
            $summeryResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $promptText],
                    // ['role' => 'system', 'content' => 'This is a transcript of a meeting that I had with a client. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call/Meeting Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps/Tasks," which should be in bullet point format. Be sure to identify which person from the "Call/Meeting Participants" is responsible for each "Next Steps/Tasks" item, break each item into individual bullet points and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => self::prepareTranscript($transcript)],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);
            $summery = $summeryResult['choices'][0]['message']['content'];
            Log::info(['summery' => $summery]);

            return $summery;
        }

        public static function generateProblemsAndGoals($transcript, $promptText){
            $problemsAndGoalsResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $promptText],
                    // ['role' => 'system', 'content' => "Help me turn the following transcript from a virtual meeting I had with a potential client into bullet points that address the client's problems and goals. The potential client is looking for help from our company, to help solve their problems and accomplish their goals with the services we offer."],

                    ['role' => 'system', 'content' => 'You will always return output in markdown format with proper line braeks.'],
                    ['role' => 'user', 'content' => $transcript],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $problemsAndGoals = $problemsAndGoalsResult['choices'][0]['message']['content'];
            Log::info(['problemsAndGoals' => $problemsAndGoals]);

            return $problemsAndGoals;
        }

        public static function generateProjectOverview($problemsAndGoals, $promptText){
            $projectOverViewResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $promptText],
                    // ['role' => 'system', 'content' => "Help me take the following points that were put together from a conversation I had with a potential client asking for our businesses to help and turn them into paragraphs. I need this text to be a very easy-to-read, well-written, and detailed project description/overview. Put into paragraph format only that is easy to read."],

                    ['role' => 'system', 'content' => 'I am sending you markdown and you will always return output in markdown format with proper line braeks with a heading Project Overview.'],
                    ['role' => 'user', 'content' => $problemsAndGoals],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $projectOverView = $projectOverViewResult['choices'][0]['message']['content'];
            Log::info(['projectOverView' => $projectOverView]);

            return $projectOverView;
        }

        public static function generateScopeOfWork($problemsAndGoals, $promptText){
            $scopeOfWorkResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $promptText],
                    // ['role' => 'system', 'content' => "I need your help in creating a very detailed bullet list for a scope of work based on the following Problems & Goals bullet list I created before. I need your help in making sure the new scope of work list you will be creating is very detailed and expanded upon as much as you can so we make sure nothing is missed for the project scope. I will end up using what you come up with in a proposal for a potential client who reached out to our company, asking us for help in the form of the services we offer. Be sure to add in quality control and testing items if not already mentioned in the list that I am providing you with. Please feel free to add the additional scope of work points that you think are missing and need to be added based on the main service the client is asking for our help with."],

                    ['role' => 'system', 'content' => 'I am sending you markdown and you will always return output in a list with array of json string. structure: [{"title":"scope of work title","details":"Scope of work details"}] follow the exact pattern without new line and tab, etc.'],
                    ['role' => 'user', 'content' => $problemsAndGoals],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $scopeOfWork = $scopeOfWorkResult['choices'][0]['message']['content'];
             Log::info(['scopeOfWork' => $scopeOfWork]);

            return self::extractJsonFromString($scopeOfWork);
        }
        public static function mergeScopeOfWork($serviceScopes, $aiScopes){
            $scopeOfWorkResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'user', 'content' => $aiScopes],
                    ['role' => 'user', 'content' => $serviceScopes],
                    ['role' => 'system', 'content' => 'Merge two JSON arrays by title. Return a single list of JSON objects with the structure [{"title":"", "details": "", "scopeId": ""}]. Exclude new lines and tabs from the output.'],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);
            //You should prioritize the user list input, where serviceId is available.

            $scopeOfWork = $scopeOfWorkResult['choices'][0]['message']['content'];
            Log::info(['mregeScopeOfWork' => $scopeOfWork]);

            return self::extractJsonFromString($scopeOfWork);
        }

        public static function generateDeliverables($scopeOfWork, $promptText){

            $deliverablesResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $promptText],
                    // ['role' => 'system', 'content' => "Help me take the following SOW list for a new project and turn them into matching \"Deliverables\" items. Remember that for every individual SOW list item, make sure you have a deliverable that matches. The formatting for this list should be in bullet point format. Then, take the same list you just created and provide me an estimate of how many hours and a timeline it would take to complete each of the deliverable items."],

                    ['role' => 'system', 'content' => 'Return a single list of JSON objects with the structure [{"title":"", "details": "", "scopeOfWorkId": ""}]. Exclude new lines and tabs from the output.'],
                    ['role' => 'user', 'content' => $scopeOfWork],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $deliverables = $deliverablesResult['choices'][0]['message']['content'];
            Log::info(['deliverables' => $deliverables]);
            return self::extractJsonFromString($deliverables);
        }

        public static function chatWithAI($content, $promptText, $context = null){

            $messages =[
                ['role' => 'user', 'content' => $content],
                ['role' => 'system', 'content' => 'Your output will be in markdown'],
            ];

            if(isset($context)){
                $messages = array_merge($context, $messages);
            }

            if($promptText){
                $messages[] = ['role' => 'system', 'content' => $promptText];
            }

            Log::info($messages);
            $chatResult = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => $messages,
                'max_tokens' => 4096,
                'temperature' => 0.5
            ]);

            $chat = $chatResult['choices'][0]['message']['content'];
            return $chat;
        }


        public static function prepareTranscript($transcript){
            // return preg_replace('/\d{2}:\d{2}\s/', '', $transcript);
            return $transcript;
        }
        /**
         * Extracts a JSON string from a given input string enclosed between ```json and ```
         * and returns it as an array.
         *
         * @param string $inputString The input string containing the JSON segment.
         * @return array|null The extracted JSON data as an array, or null if an error occurs.
         */
        public static function extractJsonFromString($inputString)
        {
            if(\Str::isJson($inputString)){
                return json_decode($inputString, true);
            }
            // Define the start and end markers
            $startMarker = '```json';
            $endMarker = '```';

            // Find the position of the start marker
            $startPos = strpos($inputString, $startMarker);

            // If the start marker is found, find the position of the end marker
            if ($startPos !== false) {
                $startPos += strlen($startMarker); // Move the starting position to the end of the start marker
                $endPos = strpos($inputString, $endMarker, $startPos);

                // If the end marker is found, extract the JSON string
                if ($endPos !== false) {
                    $jsonString = substr($inputString, $startPos, $endPos - $startPos);

                    // Trim any leading or trailing whitespace/newlines from the extracted string
                    $jsonString = trim($jsonString);

                    // Decode the JSON string to an array
                    $jsonArray = json_decode($jsonString, true);

                    // Check for JSON errors
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $jsonArray; // Successfully decoded JSON
                    } else {
                        // Log or handle JSON decoding errors as needed
                        // For example, you could use Laravel's Log facade:
                        Log::error('JSON Error: ' . json_last_error_msg());
                        return null;
                    }
                } else {
                    // Log or handle the missing end marker as needed
                    Log::error('End marker not found.');
                    return null;
                }
            } else {
                // Log or handle the missing start marker as needed
                Log::error('Start marker not found.');
                return null;
            }
        }

    }
