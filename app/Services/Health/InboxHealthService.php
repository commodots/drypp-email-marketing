<?php

namespace App\Services\Health;

class InboxHealthService
{
  public function calculate($smtp)
  {
    
    $sent = max((float)$smtp->sent_last_24h, 1.0);

    $openRate   = $smtp->opens_last_24h / $sent;
    $replyRate  = $smtp->replies_last_24h / $sent;

    $bounceRate = $smtp->bounces_last_24h / $sent;
    $failRate   = $smtp->fails_last_24h / $sent;

    // Placement Metrics (Spam vs Inbox)
    $totalHits = max((float)($smtp->inbox_hits + $smtp->spam_hits), 1.0);
    $placementScoreDecimal = $smtp->inbox_hits / $totalHits; // This is the placement score as a decimal (0-1)

    // Weighted score
   
    $score = (40 * $openRate) + (30 * $replyRate) - (20 * $bounceRate) - (30 * $failRate) + (30 * $placementScoreDecimal);

    // Normalize to 0-100
    return (float) max(0, min(100, round($score))); 
  }
}
