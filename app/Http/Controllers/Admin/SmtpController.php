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
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'encryption' => 'required|in:tls,ssl,none',
            'daily_limit' => 'required|integer|min:1'
        ]);

        SmtpServer::create([
            'name' => $request->name,
            'host' => $request->host,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $request->password,
            'encryption' => $request->encryption,
            'daily_limit' => $request->daily_limit,
            'sent_today' => 0,
            'active' => true
        ]);

        return back()->with('success', 'SMTP Server added successfully.');
    }
    public function update(Request $request, SmtpServer $smtp)
    {
        $data = $request->validate([
            'name' => 'required',
            'host' => 'required',
            'port' => 'required',
            'username' => 'required',
            'password' => 'required',
            'encryption' => 'required',
            'daily_limit' => 'required',
        ]);

        $smtp->update($data);

        return back()->with('success', 'Server updated.');
    }
    public function destroy(SmtpServer $smtp)
    {
        $smtp->delete();
        return back()->with('success', 'SMTP Server removed.');
    }
}
