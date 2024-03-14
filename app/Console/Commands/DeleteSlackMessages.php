<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DeleteSlackMessages extends Command
{
    protected $signature = 'slack:delete-messages';
    protected $description = 'Delete Slack messages within a date range';

    public function handle()
    {
        $slackToken = 'STACK_TOKEN';

        $channelId = 'C067C7YLA7Q';

        // Specify the date range
        $startDate = strtotime('2024-03-07');
        $endDate = strtotime('2024-03-08');

        // Convert dates to Slack timestamp format
        $startTimestamp = date('Y-m-d H:i:s', $startDate);
        $endTimestamp = date('Y-m-d H:i:s', $endDate);

        // Retrieve messages in the date range
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $slackToken,
        ])->get('https://slack.com/api/conversations.history', [
            'channel' => $channelId,
            'limit' => 1000,
            'text' => '.',
            'oldest' => strtotime($startTimestamp),
            'latest' => strtotime($endTimestamp),
        ]);

        // \Log::info(['Response' => $response->json()]);

        $messages = $response->json()['messages'];

        // Delete each message
        $i= 1;
        foreach ($messages as $index => $message) {
            $timestamp = $message['ts'];
            if ($message['text'] === ".") {
                $deleteResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $slackToken,
                ])->post('https://slack.com/api/chat.delete', [
                    'channel' => $channelId,
                    'ts' => $timestamp,
                ]);
                $this->info($i++.' - '.$message['text'] . ' => Deleted ? ' .$deleteResponse->json()["ok"]);

                // \Log::info([$message['ts'] => $deleteResponse->json()["ok"]]);

                if (($index + 1) % 100 === 0) {
                    $this->info("Waiting for 1 minutes...");
                    sleep(60); // Sleep for 1 minutes (60 seconds)
                }
            }
        }

        $this->info('Slack messages deleted successfully.');
    }
}
