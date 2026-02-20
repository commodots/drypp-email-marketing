@extends('layouts.app')
@section('title', 'Contacts Management')
@section('content')

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Success and Error Alerts with IDs for JS --}}
    @if(session('success'))
        <div id="successAlert" class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-lg transition-opacity duration-500">
            <p class="text-green-700 text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div id="errorAlert" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg transition-opacity duration-500">
            <ul class="list-disc list-inside text-red-700 text-sm font-bold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- Group Filter Bar --}}
    <div class="flex gap-2 overflow-x-auto pb-2">
        <a href="{{ route('contacts.index') }}" class="px-4 py-2 rounded-full text-xs font-bold border {{ !request('group') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:border-blue-400' }}">
            All Contacts
        </a>
        @foreach($groups as $group)
            <a href="{{ route('contacts.index', ['group' => $group->id]) }}" 
               class="px-4 py-2 rounded-full text-xs font-bold border transition {{ request('group') == $group->id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:border-blue-400' }}">
                {{ $group->name }}
            </a>
        @endforeach
    </div>

    <div class="flex flex-col gap-6">
        <div class="flex flex-col lg:flex-row gap-6">
            
            <div class="bg-white p-6 rounded-xl shadow-sm border w-full lg:w-1/3">
                <h3 class="font-bold text-gray-800 mb-4">
                    Add New Contact
                </h3>
                <form action="{{ route('contacts.store') }}" method="POST" class="space-y-4" id="addContactForm">
                    @csrf
                    <input type="email" name="email" placeholder="customer@example.com" class="w-full border-gray-300 rounded-lg focus:ring-blue-500" required>
                    
                    <input type="text" name="name" placeholder="John Doe" class="w-full border-gray-300 rounded-lg focus:ring-blue-500">
                    
                    <select name="group_id" class="w-full border-gray-300 rounded-lg text-sm text-gray-600">
                        <option value="">(Optional) Add to Group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>

                    {{-- Dynamic Meta Fields Section --}}
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Extra Details (Meta)</label>
                            <button type="button" onclick="addMetaField()" class="text-blue-600 hover:text-blue-700 text-xs font-bold">+ Add Field</button>
                        </div>
                        <div id="metaFieldsContainer" class="space-y-2"></div>
                    </div>
                    
                    <button class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Add Contact</button>
                </form>
            </div>

            <div class="flex flex-col gap-6 w-full lg:w-2/3">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <h3 class="font-bold text-gray-800 mb-4">
                        Create New Group
                    </h3>
                    
                    <form action="{{ route('groups.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="text" name="name" placeholder="e.g. Q1 VIPs" class="w-full border-gray-300 rounded-lg focus:ring-blue-500" required>
                        <button class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Create Group</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl border shadow-sm mb-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase">Bulk Import Contacts (CSV)</h3>
                    
                    {{-- Added ID to form for JS tracking --}}
                    <form id="importForm" action="{{ route('contacts.import') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Upload CSV (Headers: email, name, country, etc.)</label>
                            <input type="file" name="file" accept=".csv" class="w-full text-sm text-gray-500 border rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        {{-- Added ID to button --}}
                        <button id="importBtn" type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg font-bold text-sm hover:bg-black transition flex items-center justify-center min-w-[100px]">
                            Import
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Contacts List --}}
        <div class="lg:col-span-2">
            
            @if(session('imported_ids'))
                @php
                    $recentContacts = $contacts->whereIn('id', session('imported_ids'));
                @endphp
                
                @if($recentContacts->count() > 0)
                    <div id="recentImportBox" class="bg-green-50 border border-green-200 rounded-xl overflow-hidden shadow-sm mb-6 transition-opacity duration-500">
                        <div class="bg-green-100 p-3 border-b border-green-200 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-green-800">
                                Recently Imported
                            </h3>
                            <span class="bg-green-600 text-white text-xs px-2 py-1 rounded-full font-bold shadow-sm">{{ $recentContacts->count() }} records</span>
                        </div>
                        <div class="p-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach($recentContacts as $recent)
                                    <span class="inline-flex items-center gap-1 bg-white border border-green-200 text-green-800 text-xs px-3 py-1.5 rounded-md shadow-sm font-medium">
                                        {{ $recent->email }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b text-xs uppercase text-gray-500 font-bold">
                        <tr>
                            <th class="p-4">Recipient</th>
                            <th class="p-4 text-center">Groups</th>
                            <th class="p-4 text-right">Date Added</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">{{ $contact->email }}</span>
                                <span class="text-[10px] text-gray-400 font-mono uppercase">ID: {{ $contact->id }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @forelse($contact->groups as $g)
                                    <span class="inline-block bg-blue-50 text-blue-700 text-[13px] px-2 py-0.5 rounded font-bold border border-blue-100 mb-1">
                                        {{ $g->name }}
                                    </span>
                                @empty
                                    <span class="inline-block bg-gray-100 text-gray-500 text-[13px] px-2 py-0.5 rounded font-bold border border-gray-200 mb-1">No Groups</span>
                                @endforelse
                            </td>
                            <td class="p-4 text-right">
                                <span class="text-[11px] text-gray-500 font-medium">
                                    {{ $contact->created_at ? $contact->created_at->format('M d, Y') : '---' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-12 text-center text-gray-400">
                                <p class="italic">No contacts found.</p>
                                <p class="text-xs">Try clearing your filters or adding a new contact.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        
        if (successAlert) {
            successAlert.classList.add('opacity-0');
            setTimeout(() => successAlert.remove(), 500); 
        }
        
        if (errorAlert) {
            errorAlert.classList.add('opacity-0');
            setTimeout(() => errorAlert.remove(), 500);
        }
    }, 4000);

    setTimeout(() => {
        const recentBox = document.getElementById('recentImportBox');
        if (recentBox) {
            recentBox.classList.add('opacity-0');
            setTimeout(() => recentBox.remove(), 500);
        }
    }, 15000); 
});

// Logic for the Import Loading State
document.getElementById('importForm').addEventListener('submit', function() {
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    btn.innerHTML = `
        Importing...
    `;
});

// Logic for Dynamic Meta Fields
let metaFieldCount = 0;

function addMetaField() {
    metaFieldCount++;
    const container = document.getElementById('metaFieldsContainer');
    
    const fieldGroup = document.createElement('div');
    fieldGroup.className = 'flex gap-2 items-end';
    fieldGroup.innerHTML = `
        <input 
            type="text" 
            name="meta_keys[]" 
            placeholder="Field name (e.g., account_status)" 
            class="flex-1 border-gray-300 rounded-lg focus:ring-blue-500 text-sm"
            maxlength="50"
        >
        <input 
            type="text" 
            name="meta[]" 
            placeholder="Value" 
            class="flex-1 border-gray-300 rounded-lg focus:ring-blue-500 text-sm"
            maxlength="500"
        >
        <button 
            type="button" 
            onclick="this.parentElement.remove()" 
            class="text-red-500 hover:text-red-700 font-bold text-sm px-2"
        >x</button>
    `;
    
    container.appendChild(fieldGroup);
}

document.getElementById('addContactForm').addEventListener('submit', function(e) {
    const keys = document.querySelectorAll('input[name="meta_keys[]"]');
    const values = document.querySelectorAll('input[name="meta[]"]');
    
    const metaObject = {};
    
    keys.forEach((key, index) => {
        if (key.value && values[index] && values[index].value) {
            metaObject[key.value] = values[index].value;
        }
        
        key.remove();
        if(values[index]) {
            values[index].remove();
        }
    });
    
    if (Object.keys(metaObject).length > 0) {
        Object.entries(metaObject).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `meta[${key}]`;
            input.value = value;
            this.appendChild(input);
        });
    }
});
</script>
@endsection