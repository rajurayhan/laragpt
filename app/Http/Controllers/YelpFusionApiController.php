<?php

namespace App\Http\Controllers;

use App\Models\YelpAccessToken;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YelpFusionApiController extends Controller
{
    public function receiveYelpWebhook(Request $request){
        Log::info($request->all());
        if(isset($request->data['updates'])){
            $leads = $request->data['updates'];
            foreach ($leads as $key => $lead) {
                if($lead['event_type'] == 'NEW_EVENT'){
                    $leadId = $lead['lead_id'];

                    return $repliedResponse = $this->markLeadAsRepliedById($leadId);
                    Log::info($repliedResponse);
                }
            }
        }
        return response()->json(['verification' => $request->verification]);
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
            return $response->json();
        }

        return response()->json([
            'error' => 'Failed to mark lead as replied on Yelp',
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }

    public function writeLeadEventById($leadId){
        $yelpToken = $this->getAccessTokenFromRefreshToken();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $yelpToken->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.yelp.com/v3/leads/'.$leadId.'/events', [
            'request_type' => 'TEXT',
            'request_content' => 'Hi, We have received your request and will respond as soon as possible. Thanks!'
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
}
