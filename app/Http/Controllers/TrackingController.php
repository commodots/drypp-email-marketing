<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CampaignMessage;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function open($uuid)
    {
        $message = CampaignMessage::with('campaign')->where('message_uuid', $uuid)->firstOrFail();

        if ($message && !$message->opened_at) {
            $message->update(['opened_at' => now()]);
            $message->campaign()->increment('opens');
        }

        // Return a 1x1 transparent tracking pixel
        return response()->file(public_path('pixel.png'), [
            'Content-Type' => 'image/png',
        ]);
    }

    public function click(Request $request, $uuid)
    {
        $message = CampaignMessage::with('campaign')->where('message_uuid', $uuid)->firstOrFail();

        if ($message && !$message->clicked_at) {
            $message->update(['clicked_at' => now()]);
            $message->campaign()->increment('clicks');
        }

        return redirect($request->query('redirect', '/'));
    }
}