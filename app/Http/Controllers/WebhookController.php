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

            CampaignMessage::where('message_uuid', $event['custom_args']['uuid'])
                ->update([
                    'status' => $event['event'] ?? 'processed',
                ]);
        }

        return response()->json(['success' => true]);
    }
}