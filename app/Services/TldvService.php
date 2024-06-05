<?php
namespace App\Services;

use App\Libraries\WebApiResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class TldvService{

    public static function getTranscriptFromUrl($url){
        $meetingId = self::getLastPartOfUrl($url);

        if(isset($meetingId)){
            $transcript = self::getTldvTranscript($meetingId);
            return $transcript;
        }
        return false;
    }

    private static function getLastPartOfUrl($url){
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Get the path part
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : null;

        // Get the last segment of the path
        $lastSegment = basename($path);

        return $lastSegment;
    }

    private static function getTldvTranscript($id){
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
                // \Log::info(["Response" => $data]);
                $transcript = '';
                foreach ($data['data'] as $key => $content) {
                    $timeData = self::secondsToTime($content['startTime']);
                    $transcript .= $timeData['hours'].':'.$timeData['minutes'].':'.$timeData['seconds'].' ';
                    $transcript .= $content['speaker'] .': '. $content['text'];
                    $transcript.= PHP_EOL;
                    $transcript.= PHP_EOL;
                }
                // \Log::info(["Transcript" => $transcript]);
                return $transcript;
            } else {
                throw new \Exception($response->reason());
            }
        } catch (\Exception $exception) {
            throw new \Exception('Tldv Transcript fetching failed: ');
        }
    }

    private static function secondsToTime($seconds) {
        // Calculate the hours, minutes, and remaining seconds
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        // Add leading zeros to values less than 10
        $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $remainingSeconds = str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT);

        // Return the result as an array
        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $remainingSeconds
        ];
    }

}
