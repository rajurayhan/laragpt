<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class YelpFusionApiController extends Controller
{
    public function receiveYelpWebhook(Request $request){
        \Log::info(['Yelp Lead' => $request->all()]);
        return response()->json(['verification' => $request->verification]);
    }

    public function yelpInitOAuth(Request $request){
        $clientId = env('YELP_OAUTH_CLIENT_ID');
        // $clientSecret = env('YELP_OAUTH_CLIENT_SECRET');
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 15 ; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $redeirectURL = route('yelp.oauth.callback');

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
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://api.yelp.com/v3/businesses/subscriptions', [
                'client_id' => env('YELP_OAUTH_CLIENT_ID'),
                'client_secret' => env('YELP_OAUTH_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => route('yelp.oauth.callback')
            ]);

            return response()->json($response->json());
        }
    }
}
