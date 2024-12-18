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
            'icon_url' => 'https://s3-us-west-2.amazonaws.com/slack-files2/bot_icons/2022-01-19/2974093107333_48.png', // Uncomment for custom image URL (Optional)
            'username' => 'Yelp RQA', // Optional: Bot display name
            'attachments' => [
                [
                    'text' => 'Sent from HiveAI',
                    'color' => '#36a64f'
                ]
            ]
        ]);

        return $response->json();
    }
}
