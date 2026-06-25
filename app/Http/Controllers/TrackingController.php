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

        $pixelPath = public_path('pixel.png');
        
        if (file_exists($pixelPath)) {
            return response()->file($pixelPath, ['Content-Type' => 'image/png']);
        }
        
        // Fallback: return a 1x1 transparent PNG if file doesn't exist
        $fallbackPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        return response($fallbackPng, 200, ['Content-Type' => 'image/png']);
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

        $redirect = $request->query('redirect', '/');
        
        // Security: Only allow relative URLs or same-origin redirects
        if (filter_var($redirect, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($redirect);
            // Only allow http/https schemes and same host or empty host
            if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
                $redirect = '/';
            }
        }
        
        return redirect($redirect);
    }
}