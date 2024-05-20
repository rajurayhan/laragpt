<?php

namespace App\Http\Controllers\Api;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Models\ProblemsAndGoals;
use App\Models\ScopeOfWork;
use App\Models\Services;
use App\Services\OpenAIGeneratorService;
use App\Services\PromptService;
use Illuminate\Http\Request;

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

        $problemGoalsObj = ProblemsAndGoals::findOrFail($validatedData['problemGoalID']);

        $serviceList = Services::where('projectTypeId',)->get();

        $problemGoalsObj      = ProblemsAndGoals::findOrFail($request->problemGoalID);
        $scopeOfWork   = OpenAIGeneratorService::generateScopeOfWork($problemGoalsObj->problemGoalText, $prompt->prompt);
        return response()->json(json_decode(trim(trim(trim($scopeOfWork,'`'),'json'))), 201);

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
