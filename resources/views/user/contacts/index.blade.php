@extends('layouts.app')
@section('title', 'Contacts Management')
@section('content')

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Success and Error Alerts --}}
    @if(session('success'))
        <div id="successAlert" class="auto-fade bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-lg transition-opacity duration-500">
            <p class="text-green-700 text-sm font-bold">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div id="errorAlert" class="auto-fade bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg transition-opacity duration-500">
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

                    {{-- Advanced Personalization Section --}}
                    <div class="border-t pt-4 mt-4">
                        <details class="group">
                            <summary class="flex justify-between items-center font-bold cursor-pointer list-none text-sm text-gray-700 hover:text-blue-600 transition">
                                <span>+ Custom Email Variables</span>
                                <span class="transition group-open:rotate-180 text-gray-400">
                                    <svg fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="16"><path d="M6 9l6 6 6-6"></path></svg>
                                </span>
                            </summary>
                            
                            <div class="mt-4 space-y-3 bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    Add extra details here to personalize your emails later. For example, if you add a field named <code class="bg-white text-blue-700 px-1 py-0.5 rounded border font-mono">city</code> with the value <code class="bg-white text-blue-700 px-1 py-0.5 rounded border font-mono">Lagos</code>, you can type <code class="bg-white text-red-600 px-1 py-0.5 rounded border font-mono">@{{ meta.city }}</code> in the Campaign Builder to automatically insert it!
                                </p>
                                
                                <div id="metaFieldsContainer" class="space-y-2"></div>
                                
                                <button type="button" onclick="addMetaField()" class="w-full mt-2 bg-white border border-blue-200 text-blue-600 hover:bg-blue-50 text-xs font-bold py-2 rounded shadow-sm transition flex justify-center items-center gap-2">
                                    <span>+ Add Custom Field</span>
                                </button>
                            </div>
                        </details>
                    </div>
                    
                    <button type="submit" data-loading-text="Adding..." class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition mt-2">Add Contact</button>
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
                        <button type="submit" data-loading-text="Creating..." class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Create Group</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl border shadow-sm mb-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase">Bulk Import Contacts (CSV)</h3>
                    
                    {{-- Import Notice --}}
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex gap-3 items-start">
                        <svg class="text-amber-500 mt-0.5" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                        <p class="text-xs text-amber-800 leading-tight">
                            <strong>Important:</strong> If you want to import contacts to a <strong>new group</strong>, please <span class="underline">create the group above first</span>, then select it from the dropdown below. Otherwise, all contacts will be imported as "Ungrouped".
                        </p>
                    </div>

                    <form id="importForm" action="{{ route('contacts.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex flex-col md:flex-row gap-4 items-end">
                            <div class="flex-1 w-full">
                                <label class="block text-xs text-gray-500 mb-1">Select Target Group</label>
                                <select name="group_id" class="w-full border-gray-300 rounded-lg text-sm text-gray-600 focus:ring-blue-500">
                                    <option value="">No Group (Just add to Contacts)</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1 w-full">
                                <label class="block text-xs text-gray-500 mb-1">Upload CSV (Headers: email, name...)</label>
                                <input type="file" name="file" accept=".csv" class="w-full text-sm text-gray-500 border rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                            </div>

                            <button type="submit" data-loading-text="Importing..." class="bg-gray-800 text-white px-6 py-2.5 rounded-lg font-bold text-sm hover:bg-black transition min-w-[120px]">
                                Start Import
                            </button>
                        </div>
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

            {{-- Card layout for All Contacts --}}
            @if(!request('group'))
                @php
                    $ungrouped = $contacts->filter(fn($c) => $c->groups->isEmpty());
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Ungrouped Section --}}
                    @php
                        $displayUngrouped = $ungrouped->take(3);
                        $hiddenUngrouped = $ungrouped->count() - 3;
                    @endphp
                    
                    <div class="border rounded-lg shadow-sm">
                        <div class="p-3 bg-orange-50 rounded-t-lg">
                            <div>
                                <h4 class="font-bold text-orange-700 text-sm">Ungrouped ({{ $ungrouped->count() }})</h4>
                                <p class="text-xs text-orange-700">{{ $ungrouped->count() }} contacts</p>
                            </div>
                        </div>
                        <div class="p-2 space-y-2" id="ungroupedItemsContainer">
                            @forelse($displayUngrouped as $contact)
                                <div class="border border-orange-100 rounded px-2 py-1.5 bg-orange-50 text-xs space-y-1">
                                    <div class="font-semibold text-gray-800 truncate">{{ $contact->email }}</div>
                                    <div class="text-gray-500 text-xs truncate">{{ $contact->name ?: '—' }}</div>

                                    <form action="{{ route('contacts.store') }}" method="POST" class="flex gap-1 items-center pt-1">
                                        @csrf
                                        <input type="hidden" name="email" value="{{ $contact->email }}">
                                        <input type="hidden" name="name" value="{{ $contact->name }}">
                                        <select name="group_id" required class="border border-gray-300 rounded px-1 py-0.5 text-[10px] flex-1">
                                            <option value="">Select group</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded whitespace-nowrap">Add</button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-xs text-gray-500 p-1">No ungrouped contacts</div>
                            @endforelse
                            @if($hiddenUngrouped > 0)
                                <button id="ungroupedMoreBtn" type="button" data-more="{{ $hiddenUngrouped }}" onclick="toggleUngroupedList()" class="block w-full text-xs text-orange-600 hover:text-orange-800 font-semibold p-1 text-left">
                                    +{{ $hiddenUngrouped }} more
                                </button>
                            @endif
                        </div>

                        {{-- Hidden ungrouped contacts list --}}
                        @if($hiddenUngrouped > 0)
                            <div id="hiddenUngroupedList" class="hidden border-t space-y-2 p-2">
                                @foreach($ungrouped->skip(3) as $contact)
                                    <div class="border border-orange-100 rounded px-2 py-1.5 bg-orange-50 text-xs space-y-1">
                                        <div class="font-semibold text-gray-800 truncate">{{ $contact->email }}</div>
                                        <div class="text-gray-500 text-xs truncate">{{ $contact->name ?: '—' }}</div>

                                        <form action="{{ route('contacts.store') }}" method="POST" class="flex gap-1 items-center pt-1">
                                            @csrf
                                            <input type="hidden" name="email" value="{{ $contact->email }}">
                                            <input type="hidden" name="name" value="{{ $contact->name }}">
                                            <select name="group_id" required class="border border-gray-300 rounded px-1 py-0.5 text-[10px] flex-1">
                                                <option value="">Select group</option>
                                                @foreach($groups as $group)
                                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded whitespace-nowrap">Add</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @foreach($groups as $group)
                        @php
                            $groupContacts = $contacts->filter(fn($c) => $c->groups->contains('id', $group->id));
                            $displayContacts = $groupContacts->take(5);
                            $hiddenCount = $groupContacts->count() - 5;
                        @endphp

                        <div class="border rounded-lg shadow-sm">
                            <a href="{{ route('contacts.index', ['group' => $group->id]) }}" class="flex justify-between items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-t-lg transition">
                                <div>
                                    <h4 class="font-bold text-blue-800 text-sm">{{ $group->name }}</h4>
                                    <p class="text-xs text-blue-700">{{ $groupContacts->count() }} contacts</p>
                                </div>
                            </a>
                            <div class="p-2 space-y-1">
                                @forelse($displayContacts as $contact)
                                    <a href="{{ route('contacts.index', ['group' => $group->id]) }}" class="block border border-gray-200 rounded px-2 py-1 hover:border-blue-400 hover:bg-blue-50 transition text-xs">
                                        <div class="font-semibold text-gray-800 truncate">{{ $contact->email }}</div>
                                        <div class="text-gray-500 text-xs truncate">{{ $contact->name ?: '—' }}</div>
                                    </a>
                                @empty
                                    <div class="text-xs text-gray-500 p-1">No contacts</div>
                                @endforelse
                                @if($hiddenCount > 0)
                                    <a href="{{ route('contacts.index', ['group' => $group->id]) }}" class="block text-xs text-blue-600 hover:text-blue-800 font-semibold p-1">
                                        +{{ $hiddenCount }} more
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Standard Table View for Filtered Results --}}
                <div class="bg-white border rounded-xl shadow-sm overflow-x-auto">
                    <table class="w-full text-left min-w-[600px]">
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
                                    <p class="italic">No contacts found in this group.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($contacts instanceof \Illuminate\Pagination\LengthAwarePaginator && ($contacts->currentPage() > 1 || $contacts->hasMorePages()))
                    <div class="flex justify-center items-center gap-3 mt-6">
                        @if($contacts->currentPage() > 1)
                            <a href="{{ $contacts->previousPageUrl() }}" class="inline-flex items-center gap-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                                &laquo; {{ $contacts->currentPage() - 1 }}
                            </a>
                        @endif
                        
                        <span class="text-gray-700 font-bold text-sm">
                            Page {{ $contacts->currentPage() }} of {{ $contacts->lastPage() }}
                        </span>
                        
                        @if($contacts->hasMorePages())
                            <a href="{{ $contacts->nextPageUrl() }}" class="inline-flex items-center gap-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                                {{ $contacts->currentPage() + 1 }} &raquo;
                            </a>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        const recentBox = document.getElementById('recentImportBox');
        if (recentBox) {
            recentBox.classList.add('opacity-0');
            setTimeout(() => recentBox.remove(), 500);
        }
    }, 15000);
});

// Toggle ungrouped contacts hidden list
function toggleUngroupedList() {
    const hiddenList = document.getElementById('hiddenUngroupedList');
    const moreBtn = document.getElementById('ungroupedMoreBtn');
    const initialContainer = document.getElementById('ungroupedItemsContainer');

    if (!hiddenList || !moreBtn || !initialContainer) return;

    const isHidden = hiddenList.classList.contains('hidden');
    if (isHidden) {
        hiddenList.classList.remove('hidden');
        moreBtn.textContent = 'Hide';
        initialContainer.parentNode.appendChild(moreBtn);
    } else {
        hiddenList.classList.add('hidden');
        const totalHidden = moreBtn.dataset.more;
        moreBtn.textContent = `+${totalHidden} more`;
        initialContainer.appendChild(moreBtn);
    }
}

// Handle import form submit
document.getElementById('importForm').addEventListener('submit', function(e) {
    const button = this.querySelector('button[type="submit"]');
    button.textContent = button.dataset.loadingText;
    button.disabled = true;
});

// Logic for Dynamic Meta Fields
let metaFieldCount = 0;

function addMetaField() {
    metaFieldCount++;
    const container = document.getElementById('metaFieldsContainer');
    
    const fieldGroup = document.createElement('div');
    fieldGroup.className = 'flex gap-2 items-start bg-white p-2 rounded border border-blue-100 shadow-sm';
    fieldGroup.innerHTML = `
        <div class="flex-1">
            <input type="text" name="meta_keys[]" placeholder="Name (e.g. city)" class="w-full border-gray-300 rounded focus:ring-blue-500 text-xs" maxlength="50" required>
        </div>
        <div class="flex-1">
            <input type="text" name="meta[]" placeholder="Value (e.g. Lagos)" class="w-full border-gray-300 rounded focus:ring-blue-500 text-xs" maxlength="500" required>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 font-bold px-2 py-1.5 bg-red-50 hover:bg-red-100 rounded transition" title="Remove">✕</button>
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
@endpush
@endsection