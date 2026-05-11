@extends('layouts.app')
@section('title', 'Step 1: Campaign Setup')
@section('content')

    <div class="max-w-4xl mx-auto py-8">
        <div class="flex items-center gap-4 mb-8 text-sm font-bold text-gray-400">
            <span class="text-blue-600">1. Setup</span>
            <span>→</span>
            <span class="text-gray-500">2. Content</span>
            <span>→</span>
            <span class="text-gray-500">3. Review & Launch</span>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <form action="{{ route('campaigns.stepTwo') }}" method="POST">
                @csrf
                <div class="space-y-6">

                    {{-- Name & Subject --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Campaign Name</label>

                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="e.g. January Newsletter" class="w-full border-gray-300 rounded-lg shadow-sm"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>

                            <input type="text" name="subject" value="{{ old('subject') }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm" required>
                        </div>
                    </div>

                    {{-- Sender Information --}}
                    <div class="border-t pt-4">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Sender Email</label>
                                <input type="email" name="sender_email"
                                    value="{{ old('sender_email', auth()->user()->email) }}"
                                    placeholder="hello@yourcompany.com" class="w-full border-gray-300 rounded-lg shadow-sm"
                                    required>
                                <p class="mt-1 text-xs text-gray-500">This is the email address your subscribers will see.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-4">Campaign Type</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="relative border rounded-lg p-4 cursor-pointer hover:bg-gray-50 flex items-center gap-3 transition-colors">
                                <input type="radio" name="type" value="marketing"
                                    {{ old('type', 'marketing') == 'marketing' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-bold text-gray-900">Marketing Campaign</div>
                                    <div class="text-gray-500 text-sm">Send promotional or newsletter email campaigns to many subscribers.</div>
                                </div>
                            </label>

                            <label class="relative border rounded-lg p-4 cursor-pointer hover:bg-gray-50 flex items-center gap-3 transition-colors">
                                <input type="radio" name="type" value="transactional"
                                    {{ old('type') == 'transactional' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <div class="font-bold text-gray-900">Transactional Email</div>
                                    <div class="text-gray-500 text-sm">Send one-to-one transactional messages like receipts or alerts.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-4">Who should receive this?</label>

                        <div class="flex flex-col gap-3">

                            {{-- OPTION 1: GROUP --}}
                            <label
                                class="relative border rounded-lg p-4 cursor-pointer hover:bg-gray-50 flex flex-wrap items-center gap-3 transition-colors has-[:checked]:bg-blue-50 has-[:checked]:border-blue-300">

                                <input type="radio" name="recipient_type" value="group"
                                    {{ old('recipient_type') == 'group' ? 'checked' : '' }}
                                    class="peer h-4 w-4 text-blue-600 focus:ring-blue-500">

                                <span class="font-bold text-gray-900 text-sm">Send to a specific Group</span>

                                <div
                                    class="w-full hidden peer-checked:block mt-2 pt-2 border-t border-blue-200 animate-fade-in-down">
                                    <select name="group_id"
                                        class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500 bg-white">
                                        <option value="">-- Choose a Group --</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}"
                                                {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </label>


                            {{-- OPTION 2: ALL CONTACTS --}}
                            <label
                                class="relative border rounded-lg p-4 cursor-pointer hover:bg-gray-50 flex flex-wrap items-center gap-3 transition-colors has-[:checked]:bg-blue-50 has-[:checked]:border-blue-300">

                                <input type="radio" name="recipient_type" value="all"
                                    {{ old('recipient_type') == 'all' ? 'checked' : '' }}
                                    class="peer h-4 w-4 text-blue-600 focus:ring-blue-500">
                                <div class="text-sm">
                                    <span class="font-bold text-gray-900">All Contacts</span>
                                    <span class="text-gray-500 ml-2">({{ $contacts->count() }} people)</span>
                                </div>
                            </label>


                            {{-- OPTION 3: ALL EXCEPT --}}
                            <label
                                class="relative border rounded-lg p-4 cursor-pointer hover:bg-gray-50 flex flex-wrap items-center gap-3 transition-colors has-[:checked]:bg-blue-50 has-[:checked]:border-blue-300">

                                <input type="radio" name="recipient_type" value="except"
                                    {{ old('recipient_type') == 'except' ? 'checked' : '' }}
                                    class="peer h-4 w-4 text-blue-600 focus:ring-blue-500">

                                <span class="font-bold text-gray-900 text-sm">All Contacts EXCEPT...</span>

                                <div class="w-full hidden peer-checked:block mt-2 pt-2 border-t border-blue-200">
                                    <p class="text-[10px] text-red-500 font-bold mb-2 uppercase tracking-wide">
                                        Check the people you want to EXCLUDE:
                                    </p>
                                    <div
                                        class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg bg-white p-2 space-y-1 shadow-inner">
                                        @foreach ($contacts as $contact)
                                            <div class="flex items-center p-2 rounded hover:bg-gray-50">

                                                <input type="checkbox" name="excluded_contact_ids[]"
                                                    id="ex_{{ $contact->id }}" value="{{ $contact->id }}"
                                                    {{ is_array(old('excluded_contact_ids')) && in_array($contact->id, old('excluded_contact_ids')) ? 'checked' : '' }}
                                                    class="h-4 w-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                                                <label for="ex_{{ $contact->id }}"
                                                    class="ml-3 block text-xs font-medium text-gray-700 w-full cursor-pointer">
                                                    {{ $contact->email }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </label>

                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit"
                            class="bg-blue-600 text-white px-8 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md transition">
                            Next: Compose Message →
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .animate-fade-in-down {
            animation: fadeInDown 0.3s ease-out;
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
