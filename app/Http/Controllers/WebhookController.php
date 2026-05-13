<?php

namespace App\Http\Controllers;

use App\Models\CampaignMessage;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Handle SendGrid webhook events
     */
    public function sendgrid(Request $request)
    {
        foreach ($request->all() as $event) {
            if (!isset($event['custom_args']['uuid'])) {
                continue;
            }

            $message = CampaignMessage::with('smtp')->where('message_uuid', $event['custom_args']['uuid'])->first();
            
            if ($message) {
                $status = $event['event'] ?? 'processed';
                $message->update(['status' => $status]);

                if ($message->smtp) {
                    if (in_array($status, ['bounce', 'deferred', 'dropped'])) {
                        $message->smtp->increment('bounces_last_24h');
                    }
                    if ($status === 'dropped') {
                        $message->smtp->increment('fails_last_24h');
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }
}