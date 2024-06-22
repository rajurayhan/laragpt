<?php

namespace App\Jobs;

use App\Models\CalendlyEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchCalendlyEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Log::info('FetchCalendlyEventsJob started.');

        $token = env('CALENDLY_API_TOKEN');
        $url = 'https://api.calendly.com/scheduled_events';
        $dateRange = $this->determineDateRange();

        // Log::info('Date range determined:', $dateRange);

        $this->fetchAndStoreEvents($url, $token, $dateRange);

        // Log::info('FetchCalendlyEventsJob completed.');
    }

    protected function determineDateRange()
    {
        $existingEvent = CalendlyEvent::first();

        if ($existingEvent) {
            $dateRange = [
                'start_time' => Carbon::yesterday()->toIso8601String(),
                'end_time' => Carbon::now()->toIso8601String(),
            ];
            // Log::info('Existing event found, using date range from yesterday to now.', $dateRange);
            return $dateRange;
        }

        $dateRange = [
            'start_time' => Carbon::create(2020, 1, 1)->startOfDay()->toIso8601String(),
            'end_time' => Carbon::now()->toIso8601String(),
        ];
        // Log::info('No existing event found, using full date range from 2020 to now.', $dateRange);
        return $dateRange;
    }

    protected function fetchAndStoreEvents($url, $token, $dateRange)
    {
        do {
            try {
                // Build the query with organization parameter and date range
                $query = [
                    'organization' => 'https://api.calendly.com/organizations/DFECEUCMFFA4O2SP',
                    'min_start_time' => $dateRange['start_time'],
                    'max_start_time' => $dateRange['end_time'],
                    'count' => 100,
                    'status' => 'active'
                ];

                // Log::info('Fetching events from Calendly API.', ['url' => $url, 'query' => $query]);

                // Send the GET request with query parameters
                $response = Http::withToken($token)->get($url, $query);

                if ($response->successful()) {
                    $data = $response->json();
                    // Log::info('API response received.', ['data' => $data]);

                    foreach ($data['collection'] as $event) {
                        // Log::info('Processing event.', ['uri' => $event['uri']]);
                        CalendlyEvent::updateOrCreate(
                            ['uri' => $event['uri']],
                            [
                                'name' => $event['name'],
                                'meeting_notes_plain' => $event['meeting_notes_plain'],
                                'meeting_notes_html' => $event['meeting_notes_html'],
                                'status' => $event['status'],
                                'start_time' => $event['start_time'],
                                'end_time' => $event['end_time'],
                                'event_type' => $event['event_type'],
                                'location_type' => $event['location']['type'] ?? null,
                                'location' => $event['location']['location'] ?? null,
                                'additional_info' => $event['location']['additional_info'] ?? null,
                                'total_invitees' => $event['invitees_counter']['total'],
                                'active_invitees' => $event['invitees_counter']['active'],
                                'invitees_limit' => $event['invitees_counter']['limit'],
                                'created_at_api' => $event['created_at'],
                                'updated_at_api' => $event['updated_at'],
                                'user' => $event['event_memberships'][0]['user'] ?? null,
                                'user_email' => $event['event_memberships'][0]['user_email'] ?? null,
                                'user_name' => $event['event_memberships'][0]['user_name'] ?? null,
                                'guest_email' => $event['event_guests'][0]['email'] ?? null,
                                'guest_created_at' => $event['event_guests'][0]['created_at'] ?? null,
                                'guest_updated_at' => $event['event_guests'][0]['updated_at'] ?? null,
                                'calendar_kind' => $event['calendar_event']['kind'] ?? null,
                                'calendar_external_id' => $event['calendar_event']['external_id'] ?? null,
                            ]
                        );
                    }

                    // Move to the next page if available
                    $url = $data['pagination']['next_page'] ?? null;
                    // Log::info('Next page URL.', ['next_page' => $url]);
                } else {
                    // Log::error('Failed to fetch events from Calendly API.', [
                        'status' => $response->status(),
                        'error' => $response->body()
                    ]);
                    break;
                }
            } catch (\Exception $e) {
                // Log::error('Exception occurred while fetching events.', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                break;
            }
        } while ($url);
    }
}
