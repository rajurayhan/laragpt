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
            $dateRange = [
                'start_time' => Carbon::parse($lastEvent->created_at_api)->toIso8601String(),
                'end_time' => Carbon::now()->toIso8601String(),
            ];
        } else {
            $dateRange = [
                'start_time' => Carbon::create(2000, 1, 1)->startOfDay()->toIso8601String(),
                'end_time' => Carbon::now()->toIso8601String(),
            ];
        }

        return $dateRange;
    }

    protected function fetchAndStoreEvents($url, $token, $dateRange)
    {
        do {
            // Log::info($url);
            // Log::info($dateRange);
            try {
                $query = [
                    'organization' => 'https://api.calendly.com/organizations/DFECEUCMFFA4O2SP',
                    'min_start_time' => $dateRange['start_time'],
                    'max_start_time' => $dateRange['end_time'],
                    'count' => 100,
                    'status' => 'active'
                ];

                // Set the Authorization header manually
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->get($url, $query);

                if ($response->successful()) {
                    $data = $response->json();

                    // Check if there are events in the collection
                    if (!empty($data['collection'])) {
                        // Process the events
                        foreach ($data['collection'] as $key => $event) {
                            // Log::info([$key => $event['uri']]);
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

                        // Update the start_time for the next API call to the created_at of the last event fetched
                        $lastEvent = end($data['collection']);
                        $dateRange['start_time'] = Carbon::parse($lastEvent['created_at'])->toIso8601String();
                    }

                    // Get the next page URL for pagination
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

