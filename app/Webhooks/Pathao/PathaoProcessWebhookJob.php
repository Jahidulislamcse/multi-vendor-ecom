<?php

namespace App\Webhooks\Pathao;

use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class PathaoProcessWebhookJob extends ProcessWebhookJob
{
    public function handle()
    {
        $webhookPayload = $this->webhookCall->payload;

    }
}
