@extends('layouts.app')
@section('title', 'Step 2: Compose Content')

{{--Load CodeMirror Styles --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <style>
        .CodeMirror {
            height: 500px;
            border-radius: 0.75rem;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 14px;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
        }
    </style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto py-8">
    @if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
        <p class="font-bold">Please fix the following:</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    {{-- Progress Steps --}}
   <div class="flex items-center gap-4 mb-8 text-sm font-bold text-gray-400">
        <span class="text-gray-500">1. Setup</span>
        <span>→</span>
        <span class="text-blue-600">2. Content</span>
        <span>→</span>
        <span class="text-gray-500">3. Review & Launch</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        
        {{-- EDITOR COLUMN --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form action="{{ route('campaigns.stepThree') }}" method="POST" id="campaignForm">
                @csrf

                {{-- Hidden Fields to carry Step 1 Data --}}
                <input type="hidden" name="name" value="{{ $data['name'] }}">
                <input type="hidden" name="subject" value="{{ $data['subject'] }}">
                <input type="hidden" name="recipient_type" value="{{ $data['recipient_type'] }}">
                <input type="hidden" name="sender_email" value="{{ $data['sender_email'] ?? '' }}">
                @if(isset($data['group_id'])) <input type="hidden" name="group_id" value="{{ $data['group_id'] }}"> @endif
                @if(isset($data['contact_ids']))
                    @foreach($data['contact_ids'] as $id) <input type="hidden" name="contact_ids[]" value="{{ $id }}"> @endforeach
                @endif
                @if(isset($data['excluded_contact_ids']))
                    @foreach($data['excluded_contact_ids'] as $id) <input type="hidden" name="excluded_contact_ids[]" value="{{ $id }}"> @endforeach
                @endif

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email Format</label>
                        <select name="format" id="formatSelector" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="html" {{ (session('preview_format') ?? $data['format'] ?? '') == 'html' ? 'selected' : '' }}>HTML Code</option>
                            <option value="text" {{ (session('preview_format') ?? $data['format'] ?? '') == 'text' ? 'selected' : '' }}>Plain Text</option>
                        </select>
                    </div>

                    <div>
                        {{-- Dropdown and Helper Text --}}
                        <div class="flex flex-col mb-3">
                            <div class="flex justify-between items-end mb-1">
                                <label class="block text-sm font-semibold text-gray-700">Message Content</label>
                                
                                <div class="flex items-center gap-2">
                                    <select id="variableDropdown" onchange="insertFromDropdown(this)" class="text-sm border-gray-300 rounded-md py-1 pl-2 pr-8 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Insert Variable --</option>
                                        <option value="name">Name</option>
                                        <option value="email">Email Address</option>
                                        @if(isset($metaKeys) && is_array($metaKeys))
                                            @foreach($metaKeys as $key)
                                                <option value="meta.{{ $key }}">{{ str_replace('_', ' ', ucwords($key)) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-2">
                                Use the dropdown above to add data, or type it manually like <code class="bg-gray-100 text-red-600 px-1 rounded font-mono">@{{ name }}</code>, <code class="bg-gray-100 text-red-600 px-1 rounded font-mono">@{{ meta.address }}</code>, or <code class="bg-gray-100 text-red-600 px-1 rounded font-mono">@{{ meta.exact_csv_header_name }}</code>.
                            </div>
                        </div>

                        {{-- The actual textarea CodeMirror will replace --}}
                        <textarea name="body" id="codeEditor" class="hidden">{{ session('preview_body') ?? $defaultTemplate }}</textarea>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 border-t">
                        <button type="submit" name="action" value="preview" data-loading-text="Updating Preview..." class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-bold border hover:bg-gray-200 transition">
                             Update Preview
                        </button>

                        <div class="flex gap-3">
                            <button type="submit" name="action" value="draft" data-loading-text="Saving..." class="flex-1 bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-bold hover:bg-blue-50 transition">
                                Save Draft
                            </button>
                            <button type="submit" name="action" value="review" data-loading-text="Processing..." class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 transition">
                                Review Recipients
                            </button>
                        </div>
                      <div class="text-center mt-2">
                        <button type="submit" name="action" value="back" formnovalidate class="text-sm font-semibold text-gray-400 hover:text-gray-600 transition flex items-center justify-center gap-1 w-full bg-transparent border-none cursor-pointer">
                            &larr; Back to Step 1
                        </button>
                      </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- PREVIEW COLUMN --}}
        <div class="sticky top-6">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-4 tracking-tight">Live Output</h3>
            @if(session('show_preview'))
                <div class="bg-white rounded-xl shadow-lg border overflow-hidden">
                    @if(session('preview_format') == 'html')
                        <iframe class="w-full h-[600px] border-0 bg-white" 
                                srcdoc="{{ session('preview_body') ?? $defaultTemplate }}"
                                sandbox="allow-same-origin"></iframe>
                    @else
                        <div class="p-6 h-[600px] overflow-y-auto whitespace-pre-wrap font-mono text-sm text-gray-800 bg-white">
                            {{ session('preview_body') }}
                        </div>
                    @endif
                </div>
            @else
                <div class="border-2 border-dashed border-gray-200 rounded-xl h-[600px] flex flex-col items-center justify-center text-gray-400">
                    <span class="text-sm">Click "Update Preview" to render your code.</span>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- CodeMirror Logic --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    let editor; 

    document.addEventListener('DOMContentLoaded', function() {
        const textArea = document.getElementById('codeEditor');
        const formatSelector = document.getElementById('formatSelector');

        // Get the default template from PHP
        const htmlTemplate = `{!! addslashes($defaultTemplate) !!}`;
        
        // Initialize CodeMirror
        
        editor = CodeMirror.fromTextArea(textArea, {
            mode: "htmlmixed",
            theme: "dracula",
            lineNumbers: true,
            lineWrapping: true,
            tabSize: 4
        });

        // Sync Content
        editor.on('change', () => {
            editor.save(); 
        });

        let cachedHtml = ""; //Variable to act as memory for our HTML code

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

        function updateEditorMode() {
            let currentVal = editor.getValue();

            if (formatSelector.value === 'text') {
                // === SWITCHING TO TEXT MODE ===
                if (editor.getOption("mode") !== "text/plain") {
                    cachedHtml = currentVal; // Save a snapshot of the HTML before stripping
                    editor.setValue(stripHtmlToText(currentVal));
                }

                editor.setOption("mode", "text/plain");
                editor.setOption("theme", "default"); 

            } else {
                // === SWITCHING TO HTML MODE ===
                if (editor.getOption("mode") === "text/plain" && cachedHtml !== "") {
                    // Restore the snapshot if they flip back to HTML
                    editor.setValue(cachedHtml);
                } else if (currentVal.trim() === "") {
                    // Fallback to default template if the box is completely empty
                    editor.setValue(htmlTemplate);
                }
                
                editor.setOption("mode", "htmlmixed");
                editor.setOption("theme", "dracula"); 
            }
        }

        formatSelector.addEventListener('change', updateEditorMode);
        
        // Run once on load to ensure correct state (e.g. if user came back from preview)
        updateEditorMode(); 
    });

    // Handle Dropdown Selection
    window.insertFromDropdown = function(selectElement) {
        const val = selectElement.value;
        if (!val) return; 
        
        insertVariable(val);
        selectElement.selectedIndex = 0; 
    };

    // Variable Insertion Logic
    window.insertVariable = function(variableName) {
        if (!editor) return;
        
        const actualTag = '{' + '{ ' + variableName + ' }' + '}';
        const doc = editor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(actualTag, cursor);
        editor.focus();
    };

    setInterval(function() {
        fetch("{{ route('campaigns.index') }}")
            .then(response => console.log("Session refreshed"))
            .catch(error => console.error("Session refresh failed"));
    }, 300000);
</script>
@endsection