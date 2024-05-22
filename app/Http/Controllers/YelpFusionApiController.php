<?php

namespace App\Http\Controllers;

use App\Models\YelpAccessToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YelpFusionApiController extends Controller
{
    private const YELP_API_BASE_URL = 'https://api.yelp.com';
    private const YELP_OAUTH_AUTHORIZE_URL = 'https://biz.yelp.com/oauth2/authorize';
    private const YELP_OAUTH_TOKEN_URL = 'https://api.yelp.com/oauth2/token';

    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = env('YELP_OAUTH_CLIENT_ID');
        $this->clientSecret = env('YELP_OAUTH_CLIENT_SECRET');
        $this->redirectUri = env('YELP_OAUTH_CALLBACK_URI');
    }

    public function receiveYelpWebhook(Request $request)
    {
        Log::info($request->all());

        if (isset($request->data['updates'])) {
            $leads = $request->data['updates'];
            foreach ($leads as $lead) {
                if ($lead['event_type'] === 'NEW_EVENT') {
                    $leadId = $lead['lead_id'];
                    $repliedResponse = $this->markLeadAsRepliedById($leadId);
                    Log::info($repliedResponse);

                    return response()->json($repliedResponse);
                }
            }
        }

        return response()->json(['verification' => $request->verification]);
    }

    public function yelpInitOAuth(Request $request)
    {
        $randomString = bin2hex(random_bytes(8));

        $queryParams = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'leads',
            'state' => $randomString
        ];

        $yelpUrl = self::YELP_OAUTH_AUTHORIZE_URL . '?' . http_build_query($queryParams);
        return redirect()->away($yelpUrl);
    }

    public function yelpInitOAuthCallback(Request $request)
    {
        $code = $request->code;
        if ($code) {
            $response = $this->makeOAuthRequest([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri
            ]);

            if ($response->successful()) {
                $this->storeTokenData($response->json());
                return redirect()->away('https://hive.lhgdev.com/leads?status=success');
            }

            return $this->handleErrorResponse('Failed to retrieve token from Yelp', $response);
        }

        return response()->json(['error' => 'Authorization code not found'], 400);
    }

    private function makeOAuthRequest(array $params)
    {
        return Http::asForm()->post(self::YELP_OAUTH_TOKEN_URL, array_merge([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ], $params));
    }

    private function storeTokenData(array $data)
    {
        $yelpToken = YelpAccessToken::firstOrNew([]);

        $yelpToken->access_token = $data['access_token'];
        $yelpToken->expires_in = $data['expires_in'];
        $yelpToken->expires_on = Carbon::now()->addSeconds($data['expires_in']);
        $yelpToken->token_type = $data['token_type'];
        $yelpToken->refresh_token = $data['refresh_token'];
        $yelpToken->refresh_token_expires_in = $data['refresh_token_expires_in'];
        $yelpToken->refresh_token_expires_on = Carbon::now()->addSeconds($data['refresh_token_expires_in']);
        $yelpToken->scope = $data['scope'];

        $yelpToken->save();
    }

    private function getAccessToken()
    {
        $yelpToken = YelpAccessToken::first();

        // Check if access token is expired
        if (Carbon::parse($yelpToken->expires_on)->isPast()) {
            // Check if refresh token is expired
            if (Carbon::parse($yelpToken->refresh_token_expires_on)->isPast()) {
                return response()->json(['error' => 'Refresh token has expired'], 401);
            } else {
                // Refresh access token
                return $this->refreshAccessToken($yelpToken);
            }
        }

        return $yelpToken;
    }

    private function refreshAccessToken($yelpToken)
    {
        $response = $this->makeOAuthRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $yelpToken->refresh_token
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $yelpToken->access_token = $data['access_token'];
            $yelpToken->expires_in = $data['expires_in'];
            $yelpToken->expires_on = Carbon::now()->addSeconds($data['expires_in']);
            $yelpToken->save();

            return $yelpToken;
        }

        return $this->handleErrorResponse('Failed to refresh access token', $response);
    }

    private function handleErrorResponse($message, $response)
    {
        return response()->json([
            'error' => $message,
            'details' => $response->body(),
            'status' => $response->status()
        ], $response->status());
    }

    private function makeAuthenticatedRequest($method, $url, $data = [])
    {
        $yelpToken = $this->getAccessToken();
        if (is_a($yelpToken, 'Illuminate\Http\JsonResponse')) {
            return $yelpToken; // Return the error response if token retrieval failed
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $yelpToken->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->{$method}(self::YELP_API_BASE_URL . $url, $data);

        return $response;
    }

    public function markLeadAsRepliedById($leadId)
    {
        $response = $this->makeAuthenticatedRequest('post', "/v3/leads/{$leadId}/mark_as_replied", [
            'reply_type' => 'EMAIL'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return $this->handleErrorResponse('Failed to mark lead as replied on Yelp', $response);
    }

    public function writeLeadEventById($leadId)
    {
        $response = $this->makeAuthenticatedRequest('post', "/v3/leads/{$leadId}/events", [
            'request_type' => 'TEXT',
            'request_content' => 'Hi, We have received your request and will respond as soon as possible. Thanks!'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return $this->handleErrorResponse('Failed to reply on Yelp', $response);
    }

    public function getLeadDetailsById($leadId)
    {
        $response = $this->makeAuthenticatedRequest('get', "/v3/leads/{$leadId}");

        if ($response->successful()) {
            return $response->json();
        }

        return $this->handleErrorResponse('Failed to retrieve lead details from Yelp', $response);
    }
}
