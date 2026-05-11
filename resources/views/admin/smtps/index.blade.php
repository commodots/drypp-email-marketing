@extends('layouts.app')
@section('title', 'SMTP Management')
@section('content')

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">SMTP Servers</h2>
    </div>

    {{-- Add Server Form --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="font-bold mb-4">Add New Server</h3>
        <form action="{{ route('admin.smtps.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="name" placeholder="Server Name (e.g. Mailgun)" class="border-gray-300 rounded-lg px-3 py-2" required>
                <select name="type" id="smtpType" class="border-gray-300 rounded-lg px-3 py-2" required>
                    <option value="smtp" {{ old('type') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendgrid" {{ old('type') == 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                    <option value="ses" {{ old('type') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                </select>
                <input type="number" name="daily_limit" placeholder="Daily Limit" class="border-gray-300 rounded-lg px-3 py-2" required>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_transactional" value="1" class="h-4 w-4 text-blue-600 rounded border-gray-300"> Transactional
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 smtp-smtp-fields">
                <input type="text" name="host" placeholder="Host IP / URL" class="border-gray-300 rounded-lg px-3 py-2">
                <input type="text" name="port" placeholder="Port" class="border-gray-300 rounded-lg px-3 py-2">
                <input type="text" name="username" placeholder="Username" class="border-gray-300 rounded-lg px-3 py-2">
                <input type="password" name="password" placeholder="Password" class="border-gray-300 rounded-lg px-3 py-2">
                <select name="encryption" class="border-gray-300 rounded-lg px-3 py-2">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="none">None</option>
                </select>
            </div>

            <div class="smtp-sendgrid-fields hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="api_key" placeholder="SendGrid API Key" class="border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" name="region" placeholder="Region (optional)" class="border-gray-300 rounded-lg px-3 py-2">
                    <div></div>
                </div>
            </div>

            <div class="smtp-ses-fields hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="access_key" placeholder="AWS Access Key" class="border-gray-300 rounded-lg px-3 py-2">
                    <input type="password" name="secret_key" placeholder="AWS Secret Key" class="border-gray-300 rounded-lg px-3 py-2">
                    <input type="text" name="region" placeholder="AWS Region" class="border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="warmup_enabled" value="1" class="h-4 w-4 text-blue-600 rounded border-gray-300"> Enable Warmup
                </label>
                <span class="text-xs text-gray-500">Enable SMTP warmup for this server.</span>
            </div>

            <button class="bg-gray-800 text-white px-4 py-2 rounded-lg font-bold hover:bg-black">Add Server</button>
        </form>
    </div>

    {{-- Server List --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                <tr>
                    <th class="p-4">Name</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Host / Provider</th>
                    <th class="p-4">Limit</th>
                    <th class="p-4">Transactional</th>
                    <th class="p-4">Warmup</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($servers as $server)
                <tr>
                    <td class="p-4 font-bold">{{ $server->name }}</td>
                    <td class="p-4">{{ ucfirst($server->type ?? 'smtp') }}</td>
                    <td class="p-4 font-mono text-sm text-gray-600">{{ $server->host ?? ($server->type === 'sendgrid' ? 'SendGrid' : ($server->type === 'ses' ? 'Amazon SES' : 'N/A')) }}</td>
                    <td class="p-4">{{ number_format($server->daily_limit) }}</td>
                    <td class="p-4">{{ $server->is_transactional ? 'Yes' : 'No' }}</td>
                    <td class="p-4">{{ $server->warmup_enabled ? 'Yes' : 'No' }}</td>
                    <td class="p-4 text-right">
                        <form action="{{ route('admin.smtps.destroy', $server) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-600 text-xs hover:underline">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="p-4 text-center text-gray-500">No servers added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleServerFields() {
        const typeSelect = document.getElementById('smtpType');
        const smtpFields = document.querySelectorAll('.smtp-smtp-fields');
        const sendgridFields = document.querySelectorAll('.smtp-sendgrid-fields');
        const sesFields = document.querySelectorAll('.smtp-ses-fields');

        const selectedType = typeSelect.value;

        smtpFields.forEach(el => el.classList.toggle('hidden', selectedType !== 'smtp'));
        sendgridFields.forEach(el => el.classList.toggle('hidden', selectedType !== 'sendgrid'));
        sesFields.forEach(el => el.classList.toggle('hidden', selectedType !== 'ses'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('smtpType');
        if (!typeSelect) return;
        typeSelect.addEventListener('change', toggleServerFields);
        toggleServerFields();
    });
</script>
@endsection