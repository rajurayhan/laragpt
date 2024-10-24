<?php

namespace App\Http\Controllers;

use App\Enums\PromptType;
use App\Models\Prompt;
use App\Models\YelpAccessToken;
use App\Models\YelpLead;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;

class YelpFusionApiController extends Controller
{
    public function receiveYelpWebhook(Request $request){
        \Log::info($request->all());
        if(isset($request->data['updates'])){
            $leads = $request->data['updates'];
            foreach ($leads as $key => $lead) {
                if($lead['event_type'] == 'NEW_EVENT'){
                    $leadId = $lead['lead_id'];
                    // Check if lead exists with this lead id and was replied or not.
                    if($this->checkIfLeadExists($leadId)){
                        return 'Existing Lead Webhook Received';
                    }
                    // If not exists, first get the lead events and create a lead in hive system as well as into the lead tracker.
                    else{
                        $leadEvents =  $this->getYelpWebhookEvents($leadId);
                        if(isset($leadEvents['events'])){
                            $firstEvent = $leadEvents['events']['0'] ?? null;
                            // \Log::info($leadEvents);
                            if($firstEvent){
                                $this->createLead($firstEvent, $leadId);
                                $aiRespons = $this->getAIResponseforLead($firstEvent);
                                // \Log::info($aiRespons);
                                if($aiRespons){
                                    $this->writeLeadEventById($leadId, $aiRespons);
                                }
                                $repliedResponse = $this->markLeadAsRepliedById($leadId);
                                // \Log::info($repliedResponse);
                                // Create a Lead On What converts
                                return 'Lead Webhook Received and Responded';
                            }
                        }
                    }

                }
            }
        }
        return response()->json(['verification' => $request->verification]);
    }

    public function checkIfLeadExists($leadId){
        $lead = YelpLead::where('yelp_lead_id', $leadId)->first();
        if($lead){
            return true;
        }
        return false;
    }

    public function createLead($firstEvent, $leadId){
        $yelLeadRequestBody = [
            'yelp_user_id' => $firstEvent['user_id'],
            'yelp_lead_id' => $leadId,
            'initial_query_and_answers' => $firstEvent['event_content']['text'],
            'marked_as_replied' => 0,
            'marked_as_replied_at' => NULL,
            'user_display_name' =>  $firstEvent['user_display_name']
        ];

        YelpLead::create($yelLeadRequestBody);

        $whatConvertsLeadData = [
            'name' => $firstEvent['user_display_name'],
            'mapped_fields' => [
                'Contact Name' => $firstEvent['user_display_name']
            ],
            'additional_fields' => [
                'Notes' => $firstEvent['event_content']['text'],
            ],
            'lead_type' => 'chat',
            'lead_source' => 'Yelp'
        ];

        $this->createWhatConvertLead($whatConvertsLeadData);
    }

    public function yelpInitOAuth(Request $request){
        $clientId = env('YELP_OAUTH_CLIENT_ID');
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 15 ; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $redeirectURL = env('YELP_OAUTH_CALLBACK_URI');

        $baseUrl = 'https://biz.yelp.com/oauth2/authorize';
        $queryParams = [
            'client_id' => $clientId,
            'redirect_uri' => $redeirectURL,
            'response_type' => 'code',
            'scope' => 'leads',
            'state' => $randomString
        ];

        $queryString = http_build_query($queryParams);
        $yelpUrl = $baseUrl . '?' . $queryString;
        return redirect()->away($yelpUrl);
    }

    public function yelpInitOAuthCallback(Request $request){
        $code = $request->code;
        if($code){
            $response = Http::asForm()->post('https://api.yelp.com/oauth2/token', [
                'client_id' => env('YELP_OAUTH_CLIENT_ID'),
                'client_secret' => env('YELP_OAUTH_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => env('YELP_OAUTH_CALLBACK_URI')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $yelpToken = YelpAccessToken::first();

                if(!$yelpToken){
                    $yelpToken = new YelpAccessToken();
                }

                $yelpToken->access_token = $data['access_token'];
                $yelpToken->expires_in = $data['expires_in'];
                $yelpToken->expires_on = Carbon::parse($data['expires_on'])->format('Y-m-d H:m:s');
                $yelpToken->token_type = $data['token_type'];
                $yelpToken->refresh_token = $data['refresh_token'];
                $yelpToken->refresh_token_expires_in = $data['refresh_token_expires_in'];
                $yelpToken->refresh_token_expires_on = Carbon::parse($data['refresh_token_expires_on'])->format('Y-m-d H:m:s');
                $yelpToken->scope = $data['scope'];

                $yelpToken->save();

                return redirect()->away('https://hive.lhgdev.com/leads?status="success"');
            }

            return response()->json([
                'error' => 'Failed to retrieve token from Yelp',
                'details' => $response->body(),
                'status' => $response->status()
            ], $response->status());
        }
    }

    // public function checkTokenExpiry(){

    // }
    // public function checkRefreshTokenExpiry(){

    // }

    public function getAccessTokenFromRefreshToken(){
        $yelpToken = YelpAccessToken::first();
        $response = Http::asForm()->post('https://api.yelp.com/oauth2/token', [
            'client_id' => env('YELP_OAUTH_CLIENT_ID'),
            'client_secret' => env('YELP_OAUTH_CLIENT_SECRET'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $yelpToken->refresh_token,
        ]);

        \Log::info(['Refresh Token Response' => $response->body()]);

        if ($response->successful()) {
            $data =  $response->json();

            $yelpToken->access_token = $data['access_token'];
            $yelpToken->expires_in = $data['expires_in'];
            $yelpToken->expires_on = Carbon::parse($data['expires_on'])->format('Y-m-d H:m:s');

            $yelpToken->save();

            return $yelpToken;
        }

        return response()->json([
            'error' => 'Failed to get access token',
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }

    public function markLeadAsRepliedById($leadId){
        $yelpToken = $this->getAccessTokenFromRefreshToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $yelpToken->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.yelp.com/v3/leads/'.$leadId.'/mark_as_replied', [
            'reply_type' => 'EMAIL'
        ]);

        if ($response->successful()) {
            $lead = YelpLead::where('yelp_lead_id', $leadId)->first();
            $lead->marked_as_replied = TRUE;
            $lead->marked_as_replied_at = Carbon::now();
            $lead->save();

            return $response->json();
        }

        return response()->json([
            'error' => 'Failed to mark lead as replied on Yelp',
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }
    public function getYelpWebhookEvents($leadId){
        $yelpToken = $this->getAccessTokenFromRefreshToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $yelpToken->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get('https://api.yelp.com/v3/leads/'.$leadId.'/events');

        if ($response->successful()) {
            return $response->json();
        }

        return response()->json([
            'error' => 'Failed to mark lead as replied on Yelp',
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }

    public function writeLeadEventById($leadId, $response){
        $yelpToken = $this->getAccessTokenFromRefreshToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $yelpToken->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.yelp.com/v3/leads/'.$leadId.'/events', [
            'request_type' => 'TEXT',
            'request_content' => $response
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return response()->json([
            'error' => 'Failed to reply on Yelp',
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }

    public function getAIResponseforLead($leadEventData){

        $responderPrompt = Prompt::where('type', PromptType::YELP_API_CHAT_BOT)->first();
        if($responderPrompt){
            $prompt = $responderPrompt->prompt;
        }
        else{
            return null;
        }

        $messages = [
            ['role' => 'system', 'content' => $prompt],
            ['role' => 'user', 'content' => "Here is the lead details:" . json_encode($leadEventData)],
        ];
        // \Log::info($messages);

        $aiResult = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => $messages
            ]);
        $yelpRespons = $aiResult['choices'][0]['message']['content'];

        return $yelpRespons;
    }

    public function createWhatConvertsLead(array $leadData){
        $apiKey = 'YOUR_WHATCONVERTS_API_KEY';
        $accountId = 'YOUR_ACCOUNT_ID';
        $profileId = 'YOUR_PROFILE_ID';

        $url = "https://app.whatconverts.com/api/v1/leads/";

        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Accept' => 'application/json',
        ])->post($url, [
            'account_id' => $accountId,
            'date' => $leadData['date'], // Format: YYYY-MM-DD
            'time' => $leadData['time'], // Format: HH:MM:SS
            'source' => $leadData['source'],
            'medium' => $leadData['medium'],
            'campaign' => $leadData['campaign'],
            'type' => 'form', // For example, "form"
            'name' => $leadData['name'],
            'phone' => $leadData['phone'],
            'email' => $leadData['email'],
            'message' => $leadData['message'],
            'value' => $leadData['value'],
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return $response->body();
        }
    }

    public function getLeadDetailsById($leadId){
        $yelpToken = $this->getAccessTokenFromRefreshToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $yelpToken->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get('https://api.yelp.com/v3/leads/'.$leadId);

        if ($response->successful()) {
            return $response->json();
        }

        return response()->json([
            'error' => 'Failed to retrieve lead details from Yelp',
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }

    public function getWhatConvertsLeads(){
        // Retrieve the API token and secret from the environment
        $apiToken = env('WHATCONVERTS_API_TOKEN');
        $apiSecret = env('WHATCONVERTS_API_SECRET');

        // Define the base URL for the API request
        $baseUrl = 'https://leads.lhgraphics.com/api/v1/leads';

        // Set up the authorization header
        $authHeader = 'Basic ' . base64_encode($apiToken . ':' . $apiSecret);

        try {
            // Make the GET request to the WhatConverts API
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ])->get($baseUrl);

            // Check if the request was successful
            if ($response->successful()) {
                // Decode and return the response data
                $leads = $response->json();
                return response()->json($leads);
            } else {
                return response()->json([
                    'error' => 'Failed to retrieve leads',
                    'status' => $response->status(),
                    'message' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Handle any errors that may occur during the API request
            return response()->json([
                'error' => 'An error occurred while fetching leads',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createWhatConvertLead($leadData){
        // Retrieve the API token and secret from the environment
        $apiToken = env('WHATCONVERTS_API_TOKEN');
        $apiSecret = env('WHATCONVERTS_API_SECRET');

        // Define the base URL for the API request
        $baseUrl = 'https://leads.lhgraphics.com/api/v1/leads';

        // Set up the authorization header
        $authHeader = 'Basic ' . base64_encode($apiToken . ':' . $apiSecret);

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ])->asForm()->post($baseUrl, $leadData);

            // Check if the request was successful
            if ($response->successful()) {
                return response()->json([
                    'success' => 'Lead created successfully',
                    'data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to create lead',
                    'status' => $response->status(),
                    'message' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Handle any errors that may occur during the API request
            return response()->json([
                'error' => 'An error occurred while creating the lead',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function testYelpResponder(){
        $leadId = '7l9XRNPUgS_WnW44fYcHNg';
        $allEvents =  $this->getYelpWebhookEvents($leadId);
        if(isset($allEvents['events'])){
            $firstEvent = $allEvents['events']['0'] ?? null;
            if(isset($firstEvent)){
                return $aiRespons = $this->getAIResponseforLead($firstEvent);
                if($aiRespons){
                    //$this->writeLeadEventById('GtkxeBxT3Aajyf8U47g7fg', $aiRespons);
                    return response()->json($aiRespons);
                }
            }
        }
    }
}
