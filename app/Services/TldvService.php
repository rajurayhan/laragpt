<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class TldvService{

    public function getTranscriptFromUrl($url){
        $meetingId = $this->getLastPartOfUrl($url);

        if(isset($meetingId)){
            $transcript = $this->getTldvTranscript($meetingId);
            return $transcript;
        }
        return false;
    }

    private function getLastPartOfUrl($url){
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Get the path part
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : null;

        // Get the last segment of the path
        $lastSegment = basename($path);

        return $lastSegment;
    }
    private function getTldvTranscript($id){
        try {
            $apiKey = Config::get('tldv.api_key');

            $apiUrl = 'https://pasta.tldv.io/v1alpha1/meetings/'.$id.'/transcript';

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->get($apiUrl);

            // Check if the request was successful (status code 2xx)
            if ($response->successful()) {
                $data = $response->json(); // Get the response data
                $transcript = '';
                foreach ($data['data'] as $key => $content) {
                    $transcript .= $content['speaker'] .': '. $content['text'];
                    $transcript.= PHP_EOL;
                    $transcript.= PHP_EOL;
                }
                return $transcript;
            } else {
                $errorMessage = $response->status() . ' ' . $response->reason();
                return null;
            }
        } catch (\Exception $exception) {
            dd($exception);
            return null;
        }
    }

}
