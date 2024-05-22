<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Models\Services;
use App\Models\ServiceScopes;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


/**
 * @authenticated
 */

 class ScopeOfWorkController extends Controller
{
    private $promptType = PromptType::SCOPE_OF_WORK;

    /**
     * Create Scope Of Work
     *
     * @group Scope Of Work
     *
     * @bodyParam problemGoalID int required Id of the ProblemsAndGoals.
     */

    public function create(Request $request){
        set_time_limit(500);
        /*$res =
            "```json\n[\n {\"title\":\"Implement Secure Socket Layer (SSL) Certificate\",\"details\":\"Procure and install an SSL certificate to ensure website security and credibility.\"},\n {\"title\":\"Content Revision and Copywriting\",\"details\":\"Audit existing content for plagiarism and grammatical errors, and create original, professional content aligned with the company's brand voice.\"},\n {\"title\":\"Website Redesign\",\"details\":\"Develop a modern, professional website design that reflects the client's brand identity and appeals to the target demographic of 20s and 30s professionals in Texas.\"},\n {\"title\":\"Navigation and User Experience (UX) Optimization\",\"details\":\"Simplify the website structure to enhance user experience with clear navigation paths, ensuring ease of use for potential recruits.\"},\n {\"title\":\"Photography and Graphic Design Update\",\"details\":\"Source or create new, high-quality images and graphics that resonate with the local culture and professional aesthetic of the client's industry.\"},\n {\"title\":\"Hosting Security Enhancement\",\"details\":\"Migrate the website to a secure hosting environment with reliable uptime and support for the redesigned website.\"},\n {\"title\":\"Contact Information Update\",\"details\":\"Revise the contact page to include up-to-date address information, email addresses, a general phone number, and an integrated Google Maps location.\"},\n {\"title\":\"Recruitment Strategy Alignment\",\"details\":\"Remove the job application feature and add a brief section on employee benefits with links to the company's LinkedIn and Indeed profiles.\"},\n {\"title\":\"Content Consolidation\",\"details\":\"Streamline the website content by consolidating service-related information and removing unnecessary sections like CEO greetings and company history.\"},\n {\"title\":\"Simplified 'About Us' Page\",\"details\":\"Create a concise 'About Us' page with a paragraph detailing the company's mission and vision, aligning with the brand's professional image.\"},\n {\"title\":\"Vendor and Customer Section Update\",\"details\":\"Evaluate the relevance of 'Major Vendors' and 'Major Customers' sections and update or delete as necessary.\"},\n {\"title\":\"Design Aesthetic Direction\",\"details\":\"Follow a minimalist design approach with a focus on simplicity and minimal wording, as per client's preferences.\"},\n {\"title\":\"SEO Consideration\",\"details\":\"Optimize the website for user experience rather than search engine rankings, given the client's specific client relationship model.\"},\n {\"title\":\"Remove Extraneous Website Sections\",\"details\":\"Eliminate sections such as 'Community' and 'Inquiry Forms' that do not align with the client's streamlined website goals.\"},\n {\"title\":\"Budget Estimation\",\"details\":\"Provide a competitive estimate that considers the client's previous quotes and aligns with the value offered by Korean vendors.\"},\n {\"title\":\"Project Timeline Proposal\",\"details\":\"Outline a project timeline with key milestones, including a deadline for the initial estimate and proposed sitemap delivery.\"},\n {\"title\":\"Quality Control and Testing\",\"details\":\"Implement rigorous testing protocols to ensure cross-browser compatibility, mobile responsiveness, and functionality before launch.\"},\n {\"title\":\"Client Collaboration and Feedback\",\"details\":\"Establish a clear communication channel for ongoing client collaboration, feedback, and approval processes throughout the project.\"},\n {\"title\":\"Training and Handover Documentation\",\"details\":\"Provide training to the client's team on managing the website's content and create comprehensive handover documentation for future reference.\"}\n]\n```";
        dd(json_decode(trim(trim(trim($res,'`'),'json'))));*/
       /* $t = <<<EOD
```json

[

    {"title":"Project Initiation","details":"Conduct initial client meeting to discuss project objectives, expectations, and timelines. Establish primary points of contact and communication protocols."},

    {"title":"Requirements Gathering","details":"Perform detailed requirement analysis to understand the client's business, brand identity, target audience, and specific needs for the new website."},

    {"title":"SSL Certificate Implementation","details":"Procure and install an SSL certificate for the website to ensure secure data transmission and improve trust with users."},

    {"title":"Content Development","details":"Create original and authentic content tailored to the company's brand voice, ensuring it is free of grammatical errors and tailored for the target demographic."},

    {"title":"Website Redesign","details":"Design a modern, professional website interface that appeals to college students and recent graduates, focusing on usability and aesthetics."},

    {"title":"Job Openings Section Removal","details":"Eliminate the job openings section from the website and set up a redirection or clear pathway to the company's LinkedIn and Indeed profiles for recruitment purposes."},

    {"title":"Content Streamlining","details":"Remove fluff and unnecessary information from the website, ensuring all content is relevant and serves the company's needs."},

    {"title":"Community Section Removal","details":"Delete the irrelevant 'Community' section and any unneeded office photos from the website."},

    {"title":"Contact Us Section Update","details":"Update the 'Contact Us' section with current addresses and relevant contact information, removing outdated vendor/customer details."},

    {"title":"About Us Page Simplification","details":"Design a simplified 'About Us' page that effectively combines the company overview, history, and location into a single, cohesive section."},

    {"title":"Website Content Simplification","details":"Ensure the website content is concise, simple, and clean, focusing on services offered, company profile, and contact information."},

    {"title":"Business Section Streamlining","details":"Streamline the business section of the website, potentially combining services into one page, pending approval from the Branch President."},

    {"title":"Recruitment Section Development","details":"Create a recruitment section that highlights company benefits and directs users to LinkedIn for job openings."},

    {"title":"SEO Strategy Minimization","details":"Design the website without a focus on SEO, considering the company's reliance on established client relationships."},

    {"title":"Form Elimination","details":"Remove unnecessary forms from the website, setting up direct email contact options for users."},

    {"title":"Company Profile Presentation","details":"Develop a straightforward company profile section that accurately represents the company without extensive content."},

    {"title":"Cost-effective Design Solutions","details":"Explore and propose cost-effective solutions for the website redesign, mindful of the client's budget constraints."},

    {"title":"Quality Assurance","details":"Conduct thorough quality assurance testing across all aspects of the website, including security, functionality, content accuracy, and mobile responsiveness."},

    {"title":"User Experience Testing","details":"Perform user experience testing with a focus group from the target demographic to ensure the website's design and content align with user expectations."},

    {"title":"Project Documentation","details":"Prepare comprehensive documentation outlining the scope of work, design choices, content strategy, and maintenance guidelines."},

    {"title":"Training and Handover","details":"Provide necessary training to the LHG team for website management and content updates, and officially hand over the completed website."},

    {"title":"Post-Launch Support","details":"Offer post-launch support and maintenance services to address any arising issues and ensure smooth operation of the website."}

]

```
EOD;
        return dd(json_decode(trim(trim(trim(trim($t,'`'),'json')))));*/
        $prompt = PromptService::findPromptByType($this->promptType);
        if($prompt == null){
            $response = [
                'message' => 'Prompt not set for PromptType::MEETING_SUMMARY',
                'data' => []
            ];
            return response()->json($response, 422);
        }
        $validatedData = $request->validate([
            'problemGoalID' => 'required|int'
        ]);

        $problemGoalsObj = ProblemsAndGoals::with(['meetingTranscript','meetingTranscript.serviceInfo'])->findOrFail($validatedData['problemGoalID']);

        $serviceScope = ServiceScopes::where('projectTypeId',$problemGoalsObj->meetingTranscript->serviceInfo->projectTypeId)->get();

        $serviceScopeList = ($serviceScope->map(function($scope){
            return [
                'scopeId' => $scope->id,
                'title' => strip_tags($scope->name),
            ];
        }))->toJson();



        $problemGoalsObj = ProblemsAndGoals::findOrFail($request->problemGoalID);
        Log::debug(['$serviceScopeList',$serviceScopeList]);
        $aiScopes   = OpenAIGeneratorService::generateScopeOfWork($problemGoalsObj->problemGoalText, $prompt->prompt);

        Log::debug(['$aiScopes',json_encode($aiScopes)]);

        $mergedScope   = OpenAIGeneratorService::mergeScopeOfWork($serviceScopeList, json_encode($aiScopes));
        Log::debug(['$mergedScope',$mergedScope]);

        foreach($mergedScope as $scope){
            $scopeWork = new ScopeOfWork();
            $scopeWork->problemGoalID = $problemGoalsObj->id;
            $scopeWork->transcriptId = $problemGoalsObj->transcriptId;
            $scopeWork->serviceScopeId = $scope->scopeId?: null;
            $scopeWork->scopeText = $scope->details;
            $scopeWork->title = $scope->title;
            $scopeWork->save();
        }
        return response()->json($mergedScope, 201);

        $scopeOfWorkObj = ScopeOfWork::updateOrCreate(
            ['problemGoalID' => $request->problemGoalID],
            ['scopeText' => $scopeOfWork]
        );

        $response = [
            'message' => 'Created Successfully ',
            'data' => $scopeOfWorkObj,
        ];

        return response()->json($response, 201);
    }

    /**
     * Update Scope Of Work
     *
     * @group Scope Of Work
     *
     * @urlParam id int required Id of the Scope of Work.
     * @bodyParam scopeText string required text of the Scope of Work.
     */

    public function update($id, Request $request){
        $validatedData = $request->validate([
            'scopeText' => 'required|string'
        ]);

        $scopeOfWork = ScopeOfWork::findOrFail($id);
        $scopeOfWork->scopeText = $request->scopeText;

        $scopeOfWork->save();

        $response = [
            'message' => 'Created Successfully ',
            'data' => $scopeOfWork,
        ];

        return response()->json($response, 201);
    }
}
