<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CampaignMessage;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function open($uuid)
    {
        $message = CampaignMessage::with(['campaign', 'smtp'])->where('message_uuid', $uuid)->firstOrFail();

        if (!$message->opened_at) {
            $message->update(['opened_at' => now()]);
            $message->campaign()->increment('opens');
            
            if ($message->smtp) {
                $message->smtp->increment('opens_last_24h');
            }
        }

        return response()->file(public_path('pixel.png'), ['Content-Type' => 'image/png']);
    }

    public function click(Request $request, $uuid)
    {
        $message = CampaignMessage::with(['campaign', 'smtp'])->where('message_uuid', $uuid)->firstOrFail();

        if (!$message->clicked_at) {
            $message->update(['clicked_at' => now()]);
            $message->campaign()->increment('clicks');

            if ($message->smtp) {
                $message->smtp->increment('clicks_last_24h');
            }
        }

        return redirect($request->query('redirect', '/'));
    }
}