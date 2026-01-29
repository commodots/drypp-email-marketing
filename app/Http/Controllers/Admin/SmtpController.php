<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpServer;
use Illuminate\Http\Request;

class SmtpController extends Controller
{
    public function index()
    {
        $servers = SmtpServer::all();
        return view('admin.smtps.index', compact('servers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'daily_limit' => 'required|integer|min:1'
        ]);

        SmtpServer::create([
            'name' => $request->name,
            'host' => $request->host,
            'daily_limit' => $request->daily_limit,
            'sent_today' => 0,
            'active' => true
        ]);

        return back()->with('success', 'SMTP Server added successfully.');
    }
    public function destroy(SmtpServer $smtp)
    {
        $smtp->delete();
        return back()->with('success', 'SMTP Server removed.');
    }
}