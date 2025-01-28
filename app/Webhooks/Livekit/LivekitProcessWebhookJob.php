<?php

namespace App\Webhooks\Livekit;

use Livekit\WebhookEvent;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class LivekitProcessWebhookJob extends ProcessWebhookJob
{
    public function handle()
    {
        $webhookPayload = $this->webhookCall->payload;

        $event = new WebhookEvent($webhookPayload);

        $eventName = $event->getEvent();

        switch ($eventName) {
            case 'room_started':
                $event->getRoom()->getMetadata();
                break;
            case 'room_finished':
                $event->getRoom()->getMetadata();
                break;
            case 'participant_joined':
                $event->getParticipant()->getIdentity();
                break;
            case 'participant_left':
                $event->getParticipant()->getIdentity();
                break;
            case 'track_published':
                break;
            case 'track_unpublished':
                break;
            case 'egress_started':
                break;
            case 'egress_updated':
                break;
            case 'egress_ended':
                break;
            case 'ingress_started':
                break;
            case 'ingress_ended':
                break;
        }
    }
}
