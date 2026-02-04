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
                        <select name="format" id="formatSelector" class="w-full border-gray-300 rounded-lg shadow-sm">
                            <option value="html" {{ (session('preview_format') ?? $data['format'] ?? '') == 'html' ? 'selected' : '' }}>HTML Code</option>
                            <option value="text" {{ (session('preview_format') ?? $data['format'] ?? '') == 'text' ? 'selected' : '' }}>Plain Text</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Message Content</label>
                        {{-- The actual textarea CodeMirror will replace --}}
                        <textarea name="body" id="codeEditor" class="hidden">{{ session('preview_body') ?? $defaultTemplate }}</textarea>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 border-t">
                        <button type="submit" name="action" value="preview" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-bold border hover:bg-gray-200 transition">
                             Update Preview
                        </button>

                        <div class="flex gap-3">
                            <button type="submit" name="action" value="draft" class="flex-1 bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-bold hover:bg-blue-50 transition">
                                Save Draft
                            </button>
                            <button type="submit" name="action" value="review" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md transition">
                                Review Recipients
                            </button>
                        </div>
                      <div class="text-center mt-2">
    <button 
        type="submit" 
        name="action" 
        value="back" 
        formnovalidate 
        class="text-sm font-semibold text-gray-400 hover:text-gray-600 transition flex items-center justify-center gap-1 w-full bg-transparent border-none cursor-pointer"
    >
        <- Back to Step 1
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
    document.addEventListener('DOMContentLoaded', function() {
        const textArea = document.getElementById('codeEditor');
        const formatSelector = document.getElementById('formatSelector');

        // Get the default template from PHP
        const htmlTemplate = `{!! addslashes($defaultTemplate) !!}`;
        
        // 2. Initialize CodeMirror
        const editor = CodeMirror.fromTextArea(textArea, {
            mode: "htmlmixed",
            theme: "dracula",
            lineNumbers: true,
            lineWrapping: true,
            tabSize: 4
        });

        // 3. Sync Content
        editor.on('change', () => {
            editor.save(); 
        });

        // Handle Format Toggle
        function updateEditorMode() {
            if (formatSelector.value === 'text') {
                // === SWITCHING TO TEXT MODE ===
                editor.setOption("mode", "text/plain");
                editor.setOption("theme", "default"); 
                
            
                // If the editor currently holds the HTML template, clear it.
                // We use trim() to ignore invisible spaces/newlines.
                if (editor.getValue().trim() === htmlTemplate.trim()) {
                    editor.setValue(""); 
                }

            } else {
                // === SWITCHING TO HTML MODE ===
                editor.setOption("mode", "htmlmixed");
                editor.setOption("theme", "dracula"); 

                // If the box is empty, bring back the HTML template
                if (editor.getValue().trim() === "") {
                    editor.setValue(htmlTemplate);
                }
            }
        }

        formatSelector.addEventListener('change', updateEditorMode);
        
        // Run once on load to ensure correct state (e.g. if user came back from preview)
        updateEditorMode(); 
    });

    setInterval(function() {
        fetch("{{ route('campaigns.index') }}")
            .then(response => console.log("Session refreshed"))
            .catch(error => console.error("Session refresh failed"));
    }, 300000);
</script>
@endsection