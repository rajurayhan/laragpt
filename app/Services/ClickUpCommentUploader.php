<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickUpCommentUploader
{
    private $apiToken;
    private $taskId;
    private $commentContent;

    public function __construct($taskId, $commentContent)
    {
        $this->apiToken = env('CLICKUP_API_KEY', 'pk_49059276_OJMEQ5CGCS2HRCJL0GPY9TZOKPCZTI40');
        $this->taskId = $taskId;
        $this->commentContent = $commentContent;
    }

    public function pushComment()
    {
        $baseUrl = "https://api.clickup.com/api/v2/task/";
        $url = $baseUrl . $this->taskId . "/comment";

        $payload = [
            "comment_text" => $this->commentContent,
        ];

        $response = Http::withHeaders([
            "Authorization" => 'pk_49059276_OJMEQ5CGCS2HRCJL0GPY9TZOKPCZTI40',
            "Content-Type" => "application/json"
        ])->post($url , $payload);
        \Log::info(['ClickUp' => $response->json()]);
        return $response->json();
    }
}

// Usage example
// $apiToken = "YOUR_API_TOKEN";
// $taskId = "TASK_ID";
// $commentContent = "Your comment here";

// $clickUpUploader = new ClickUpCommentUploader($apiToken, $taskId, $commentContent);
// $response = $clickUpUploader->pushComment();

// print_r($response);
