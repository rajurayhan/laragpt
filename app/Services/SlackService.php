<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SlackService
{
    protected $token;

    public function __construct()
    {
        $this->token = env('SLACK_BOT_TOKEN');
    }

    public function sendMessageToChannel($channelId, $message)
    {
        $response = Http::withToken($this->token)->post('https://slack.com/api/chat.postMessage', [
            'channel' => $channelId,
            'text' => $message,
        ]);

        return $response->json();
    }
}
