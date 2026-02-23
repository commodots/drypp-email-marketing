@extends('layouts.app')
@section('title', 'Edit Campaign Draft')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <style>
        .CodeMirror {
            height: 400px;
            border-radius: 0.5rem;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 14px;
            border: 1px solid #e2e8f0;
        }
        .CodeMirror-readOnly {
            opacity: 0.7;
            background-color: #f8fafc; 
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Edit Draft: {{ $campaign->name }}</h2>
        <a href="{{ route('campaigns.index') }}" class="text-gray-500 hover:underline">Cancel</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form id="campaignEditForm" action="{{ route('campaigns.update', $campaign) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                
                {{-- Campaign Name & Subject Row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Campaign Name</label>
                        <input type="text" name="name" value="{{ $campaign->name }}" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Subject Line</label>
                        <input type="text" name="subject" value="{{ $campaign->emailContent->subject ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                    </div>
                </div>

                {{-- Recipient Selection --}}
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-gray-700">Audience Selection</h3>
                        <button type="button" onclick="document.getElementById('edit_audience_fields').classList.toggle('hidden')" class="text-xs text-blue-600 font-bold hover:underline transition">
                            Change Recipients &darr;
                        </button>
                    </div>

                    {{-- Current Audience Display --}}
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span>Current Recipients:</span>
                        <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide border border-blue-200">
                            @if($campaign->recipient_type === 'all')
                                All Contacts
                            @elseif($campaign->recipient_type === 'group')
                                {{ $groups->firstWhere('id', $campaign->group_id)->name ?? 'Deleted Group' }}
                            @elseif($campaign->recipient_type === 'except')
                                All Contacts (Except {{ count($campaign->excluded_contact_ids ?? []) }})
                            @else
                                Not Set
                            @endif
                        </span>
                    </div>

                    {{-- Edit Fields (Hidden by default) --}}
                    <div id="edit_audience_fields" class="hidden mt-4 pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-gray-500">Send To</label>
                                <select name="recipient_type" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500" onchange="document.getElementById('group_wrapper').style.display = this.value === 'group' ? 'block' : 'none'">
                                    <option value="all" {{ $campaign->recipient_type == 'all' ? 'selected' : '' }}>All Contacts</option>
                                    <option value="group" {{ $campaign->recipient_type == 'group' ? 'selected' : '' }}>Specific Group</option>
                                </select>
                            </div>
                            
                            <div id="group_wrapper" style="display: {{ $campaign->recipient_type == 'group' ? 'block' : 'none' }};">
                                <label class="block text-xs font-semibold mb-1 text-gray-500">Select Group</label>
                                <select name="group_id" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Choose Group --</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ $campaign->group_id == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Format Toggle --}}
                <div>
                    <label class="block text-sm font-semibold mb-2">Email Format</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="format" value="html" {{ ($campaign->emailContent->format ?? 'html') == 'html' ? 'checked' : '' }} class="text-blue-600"> 
                            <span class="text-sm font-medium">HTML Code</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="format" value="text" {{ ($campaign->emailContent->format ?? 'html') == 'text' ? 'checked' : '' }} class="text-blue-600"> 
                            <span class="text-sm font-medium">Plain Text</span>
                        </label>
                    </div>
                </div>

                {{-- Body --}}
                <div>
                    <label class="block text-sm font-semibold mb-1">Email Content</label>
                    <textarea name="body" id="codeEditor" class="hidden">{{ $campaign->emailContent->body ?? '' }}</textarea>
                </div>

                {{-- Action Bar --}}
                <div class="flex justify-between items-center gap-4 pt-4 border-t mt-4">
                    <button type="button" onclick="if(confirm('Are you sure you want to delete this draft? This cannot be undone.')) document.getElementById('delete-form').submit();" class="text-red-500 hover:text-red-700 text-sm font-bold">
                        Delete Draft
                    </button>

                    <button type="submit" data-loading-text="Saving Changes..." class="bg-blue-600 text-white px-8 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>

        {{-- Hidden form securely handles the DELETE request --}}
        <form id="delete-form" action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

{{-- CodeMirror Engine --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textArea = document.getElementById('codeEditor');
        const formatRadios = document.querySelectorAll('input[name="format"]');
        const editForm = document.getElementById('campaignEditForm');
        
        let editor = CodeMirror.fromTextArea(textArea, {
            mode: "htmlmixed",
            theme: "dracula",
            lineNumbers: true,
            lineWrapping: true,
            tabSize: 4
        });

        editor.on('change', () => { editor.save(); });

        let cachedHtml = ""; // Variable to act as memory for our HTML code

        function stripHtmlToText(html) {
            let text = html.replace(/<br\s*\/?>/gi, '\n')
                           .replace(/<\/p>/gi, '\n\n')
                           .replace(/<\/h[1-6]>/gi, '\n\n')
                           .replace(/<\/div>/gi, '\n')
                           .replace(/<li>/gi, '- ')
                           .replace(/<\/li>/gi, '\n');
            let tmp = document.createElement("div");
            tmp.innerHTML = text;
            return (tmp.textContent || tmp.innerText || "").trim();
        }

        function getSelectedFormat() {
            return document.querySelector('input[name="format"]:checked').value;
        }

        function updateEditorMode() {
            let format = getSelectedFormat();
            let currentVal = editor.getValue();

            if (format === 'text') {
                if (editor.getOption("mode") !== "text/plain") {
                    // Save a snapshot of the HTML right before we destroy it
                    cachedHtml = currentVal; 
                    editor.setValue(stripHtmlToText(currentVal));
                }
                editor.setOption("mode", "text/plain");
                editor.setOption("theme", "default"); 
            } else {
                if (editor.getOption("mode") === "text/plain" && cachedHtml !== "") {
                    //Restore the snapshot if they flip back to HTML
                    editor.setValue(cachedHtml);
                }
                editor.setOption("mode", "htmlmixed");
                editor.setOption("theme", "dracula"); 
            }
        }

        formatRadios.forEach(radio => radio.addEventListener('change', updateEditorMode));
        updateEditorMode(); 

        if (editForm) {
            editForm.addEventListener('submit', function() {
                
                editor.setOption('readOnly', 'nocursor');
                
                
                const inputs = editForm.querySelectorAll('input, select');
                inputs.forEach(input => {
                    
                    input.setAttribute('readonly', true);
                    input.style.pointerEvents = 'none';
                });
            });
        }
    });
</script>
@endsection