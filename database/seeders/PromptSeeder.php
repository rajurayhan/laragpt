<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\PromptType;
use App\Services\PromptService;

class PromptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prompts = [
            PromptType::PROJECT_SUMMARY->value => [
                'name' => 'PROJECT_SUMMARY',
                'prompt' => 'This is a transcript of a sales call with a potential new client for our other company, Defense Acquisition Solutions Group. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps," which should be in bullet point format. Be sure to identify which person from the "Call Participants" is responsible for each "Next Steps" item and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'
            ],
            PromptType::PROBLEMS_AND_GOALS->value => [
                'name' => 'PROBLEMS_AND_GOALS',
                'prompt' => "Help me turn the following transcript from a virtual meeting I had with a potential client into bullet points that address the client's problems and goals. The potential client is looking for help from our company, to help solve their problems and accomplish their goals with the services we offer."
            ],
            PromptType::PROJECT_OVERVIEW->value => [
                'name' => 'PROJECT_OVERVIEW',
                'prompt' => 'Help me take the following points that were put together from a conversation I had with a potential client asking for our businesses to help and turn them into paragraphs. I need this text to be a very easy-to-read, well-written, and detailed project description/overview. Put into paragraph format only that is easy to read.'
            ],
            PromptType::SCOPE_OF_WORK->value => [
                'name' => 'SCOPE_OF_WORK',
                'prompt' => 'I need your help in creating a very detailed bullet list for a scope of work based on the following Problems & Goals bullet list I created before. I need your help in making sure the new scope of work list you will be creating is very detailed and expanded upon as much as you can so we make sure nothing is missed for the project scope. I will end up using what you come up with in a proposal for a potential client who reached out to our company, asking us for help in the form of the services we offer. Be sure to add in quality control and testing items if not already mentioned in the list that I am providing you with. Please feel free to add the additional scope of work points that you think are missing and need to be added based on the main service the client is asking for our help with.'
            ],
            PromptType::DELIVERABLES->value => [
                'name' => 'DELIVERABLES',
                'prompt' => 'Help me take the following SOW list for a new project and turn them into matching \"Deliverables\" items. Remember that for every individual SOW list item, make sure you have a deliverable that matches. The formatting for this list should be in bullet point format. Then, take the same list you just created and provide me an estimate of how many hours and a timeline it would take to complete each of the deliverable items.'
            ],
            PromptType::MEETING_SUMMARY->value => [
                'name' => 'MEETING_SUMMARY',
                'prompt' => 'This is a transcript of a meeting that I had with a client. Can you help me turn the transcript into a summary of the call/meeting and what we discussed? The format should include "Call/Meeting Participants," which should be in bullet point format; "Meeting Summary," which should be in multiple paragraph format; and "Next Steps/Tasks," which should be in bullet point format. Be sure to identify which person from the "Call/Meeting Participants" is responsible for each "Next Steps/Tasks" item, break each item into individual bullet points and give some additional context and information in order to make sure each Next Step is clear for the person that needs to complete it.'
            ],
        ];

        foreach($prompts as $type => $prompt){
            PromptService::create($type, $prompt);
        }


    }
}
