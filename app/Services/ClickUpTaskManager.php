<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClickUpTaskManager
{
    private $apiUrl;
    private $apiToken;

    public function __construct()
    {
        $this->apiUrl = "https://api.clickup.com/api/v2";
        $this->apiToken = env('CLICKUP_API_KEY');
    }

    public function createTask($listId, $taskData)
    {
        $query = [
            "custom_task_ids" => "true",
            "team_id" => "123"
        ];

        $url = "{$this->apiUrl}/list/{$listId}/task?" . http_build_query($query);

        $response = Http::withHeaders([
            "Authorization" => $this->apiToken,
            "Content-Type" => "application/json"
        ])->post($url, $taskData);

        if ($response->failed()) {
            throw new \Exception("Error creating task: " . $response->body());
        }

        return $response->json();
    }
}
