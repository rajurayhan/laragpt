<?php

namespace App\Libraries;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ContentGenerator{ 
    public static function completion(String $prompt){
        $result = OpenAI::completions()->create([
            'model' => 'text-davinci-003', 
            'prompt' => $prompt, 
            'max_tokens' => 200,
        ]); 
        return $result;
    }

    public static function image(Request $request){
        $response = OpenAI::images()->create([
            'prompt' => $request->prompt,
            'n' => 1,
            'size' => '512x512',
            'response_format' => 'url',
        ]);
        
        $response->created; // 1589478378
        
        foreach ($response->data as $data) {
            $data->url; // 'https://oaidalleapiprodscus.blob.core.windows.net/private/...'
            $data->b64_json; // null
        }
        
        return $response->toArray(); 
    }
}