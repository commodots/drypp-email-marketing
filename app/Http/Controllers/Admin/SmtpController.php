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
            'type' => 'required|in:smtp,sendgrid,ses',
            'host' => 'nullable|required_if:type,smtp|string|max:255',
            'port' => 'nullable|required_if:type,smtp|integer|min:1|max:65535',
            'username' => 'nullable|required_if:type,smtp|string|max:255',
            'password' => 'nullable|required_if:type,smtp|string',
            'encryption' => 'nullable|required_if:type,smtp|in:tls,ssl,none',
            'api_key' => 'nullable|required_if:type,sendgrid|string',
            'region' => 'nullable|required_if:type,sendgrid,ses|string|max:255',
            'access_key' => 'nullable|required_if:type,ses|string|max:255',
            'secret_key' => 'nullable|required_if:type,ses|string',
            'daily_limit' => 'required|integer|min:1',
            'is_transactional' => 'nullable|boolean',
            'warmup_enabled' => 'nullable|boolean',
        ]);

        SmtpServer::create([
            'name' => $request->name,
            'type' => $request->type,
            'host' => $request->host,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $request->password,
            'encryption' => $request->encryption,
            'api_key' => $request->api_key,
            'region' => $request->region,
            'access_key' => $request->access_key,
            'secret_key' => $request->secret_key,
            'daily_limit' => $request->daily_limit,
            'sent_today' => 0,
            'active' => true,
            'is_transactional' => $request->boolean('is_transactional'),
            'warmup_enabled' => $request->boolean('warmup_enabled'),
        ]);

        return back()->with('success', 'SMTP Server added successfully.');
    }
    public function update(Request $request, SmtpServer $smtp)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:smtp,sendgrid,ses',
            'host' => 'nullable|required_if:type,smtp|string|max:255',
            'port' => 'nullable|required_if:type,smtp|integer|min:1|max:65535',
            'username' => 'nullable|required_if:type,smtp|string|max:255',
            'password' => 'nullable|required_if:type,smtp|string',
            'encryption' => 'nullable|required_if:type,smtp|in:tls,ssl,none',
            'api_key' => 'nullable|required_if:type,sendgrid|string',
            'region' => 'nullable|required_if:type,sendgrid,ses|string|max:255',
            'access_key' => 'nullable|required_if:type,ses|string|max:255',
            'secret_key' => 'nullable|required_if:type,ses|string',
            'daily_limit' => 'required|integer|min:1',
            'is_transactional' => 'nullable|boolean',
            'warmup_enabled' => 'nullable|boolean',
        ]);

        $data['is_transactional'] = $request->boolean('is_transactional');
        $data['warmup_enabled'] = $request->boolean('warmup_enabled');

        $smtp->update($data);

        return back()->with('success', 'Server updated.');
    }
    public function destroy(SmtpServer $smtp)
    {
        $smtp->delete();
        return back()->with('success', 'SMTP Server removed.');
    }
}
