<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campaign;
use App\Models\SmtpServer;

class DashboardController extends Controller
{
  public function index()
  {
    return view('admin.dashboard', [
      'totalUsers' => User::where('role', 'user')->count(),
      'activeCampaigns' => Campaign::whereIn('status', ['queued', 'sending'])->count(),
      'emailsToday' => SmtpServer::sum('sent_today'),
      'smtpHealth' => SmtpServer::where('active', true)->count()
    ]);
  }
}
