<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class YelpFusionApiController extends Controller
{
    public function receiveYelpWebhook(Request $request){
        \Log::info(['Yelp Lead' => $request->all()]);
        return response()->json(['verification' => $request->verification]);
    }
}
