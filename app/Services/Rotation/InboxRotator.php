<?php

namespace App\Services\Rotation;

class InboxRotator
{
    public function pick($campaign)
    {
        $smtps = $campaign->smtps()
            ->where('is_active', 1)
            ->where('is_blocked', 0)
            ->whereColumn('sent_today', '<', 'daily_limit')
            ->whereColumn('sent_this_hour', '<', 'hourly_limit')
            ->orderByDesc('priority')
            ->orderBy('sent_today')
            ->get();

        if ($smtps->isEmpty()) {
            return null;
        }

        // Smart rotation: least used wins
        return $smtps->sortBy('sent_this_hour')->first();
    }
}