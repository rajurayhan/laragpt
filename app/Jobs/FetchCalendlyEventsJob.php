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
        $token = env('CALENDLY_API_TOKEN');
        $url = 'https://api.calendly.com/scheduled_events';
        $dateRange = $this->determineDateRange();

        $this->fetchAndStoreEvents($url, $token, $dateRange);
    }

    protected function determineDateRange()
    {
        $lastEvent = CalendlyEvent::orderBy('created_at_api', 'desc')->first();

        if ($lastEvent) {
            // Use the created_at_api of the last event fetched as min_start_time
            $minStartTime = Carbon::parse($lastEvent->created_at_api)->toIso8601String();
        } else {
            // Default to fetching events from January 1, 2020
            $minStartTime = Carbon::create(2000, 1, 1)->startOfDay()->toIso8601String();
        }

        // max_start_time should be set to current time in UTC
        $maxStartTime = Carbon::now()->utc()->toIso8601String();

        return [
            'min_start_time' => $minStartTime,
            'max_start_time' => $maxStartTime,
        ];
    }

    protected function fetchAndStoreEvents($url, $token, $dateRange)
    {
        do {
            try {
                $query = [
                    'organization' => 'https://api.calendly.com/organizations/DFECEUCMFFA4O2SP',
                    'min_start_time' => $dateRange['start_time'],
                    'max_start_time' => $dateRange['end_time'],
                    'count' => 100,
                    'status' => 'active'
                ];

                $response = Http::withToken($token)->get($url, $query);

                if ($response->successful()) {
                    $data = $response->json();

                    foreach ($data['collection'] as $event) {
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

                    $url = $data['pagination']['next_page'] ?? null;
                } else {
                    Log::error('Failed to fetch events from Calendly API.', [
                        'status' => $response->status(),
                        'error' => $response->body()
                    ]);
                    break;
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred while fetching events.', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                break;
            }
        } while ($url);
    }
}

