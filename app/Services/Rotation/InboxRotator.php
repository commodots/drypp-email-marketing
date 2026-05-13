<?php

namespace App\Services\Rotation;

use App\Models\SmtpServer;

class InboxRotator
{
  public function pick($campaign = null)
  {
    $query = SmtpServer::query()
      ->where('active', 1)
      ->where('is_blocked', 0 )
      ->where('health_score', '>', 30); // Guard rails

    // If a campaign is passed, respect its specific daily/hourly limits
    if ($campaign) {
      $query->whereColumn('sent_today', '<', 'daily_limit')
        ->whereColumn('sent_this_hour', '<', 'hourly_limit');
    }

    return $query->orderByDesc('health_score') // Best first
      ->orderBy('sent_this_hour')           // Then least used this hour
      ->first();
  }
  public function getNextAvailableInbox()
  {
    return $this->pick();
  }
}
