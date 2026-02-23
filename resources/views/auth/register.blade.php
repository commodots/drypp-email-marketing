<x-guest-layout>
    <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900">
        {{-- LEFT SIDE --}}
        <div class="hidden lg:flex w-1/2 relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 items-center justify-center text-white p-12">
            <div class="absolute inset-0 opacity-20 animate-pulse bg-[radial-gradient(circle_at_30%_30%,white,transparent_40%)]"></div>
            <div class="relative z-10 max-w-md text-center">
                <img src="{{ asset('images/drypp_logo.png') }}" class="h-20 mx-auto mb-8">
                <h1 class="text-3xl font-bold mb-4">Join Drypp</h1>
                <p class="text-blue-100 leading-relaxed">Start sending smarter campaigns and scale your outreach today.</p>
            </div>
        </div>

        {{-- RIGHT SIDE --}}
        <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Create Account</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-8">Get started with your free workspace</p>

                <form method="POST" action="{{ route('register') }}" class="space-y-5" id="registerForm">
                    @csrf

                    {{-- Name --}}
                    <div class="relative">
                        <x-text-input id="name" class="peer w-full border-gray-300 rounded-lg px-4 pt-5 pb-2 focus:border-blue-600 focus:ring-0" type="text" name="name" :value="old('name')" required autofocus placeholder=" " />
                        <label for="name" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-blue-600">Full Name</label>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    {{-- Email --}}
                    <div class="relative">
                        <x-text-input id="email" class="peer w-full border-gray-300 rounded-lg px-4 pt-5 pb-2 focus:border-blue-600 focus:ring-0" type="email" name="email" :value="old('email')" required placeholder=" " />
                        <label for="email" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-blue-600">Email Address</label>
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    {{-- Password --}}
                    <div class="relative">
                        <x-text-input id="password" class="peer w-full border-gray-300 rounded-lg px-4 pt-5 pb-2 focus:border-blue-600 focus:ring-0 transition-colors duration-300" type="password" name="password" required placeholder=" " onkeyup="validatePassword(this.value)" />
                        <label for="password" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-blue-600">Password</label>
                        <button type="button" onclick="toggleVisibility('password', 'password-icon')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 transition">
                            <span id="password-icon">👁️</span>
                        </button>
                    </div>

                    {{-- Requirements Checklist --}}
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700 space-y-1">
                        <p class="text-[10px] font-bold uppercase text-gray-400 mb-1 tracking-wider">Password Requirements</p>
                        <div id="check-length" class="text-xs text-gray-500 flex items-center gap-2 transition-colors">
                            <span class="dot w-1.5 h-1.5 rounded-full bg-gray-300 transition-colors"></span> At least 8 characters
                        </div>
                        <div id="check-upper" class="text-xs text-gray-500 flex items-center gap-2 transition-colors">
                            <span class="dot w-1.5 h-1.5 rounded-full bg-gray-300 transition-colors"></span> Uppercase & lowercase
                        </div>
                        <div id="check-number" class="text-xs text-gray-500 flex items-center gap-2 transition-colors">
                            <span class="dot w-1.5 h-1.5 rounded-full bg-gray-300 transition-colors"></span> A number & a symbol
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="relative">
                        <x-text-input id="password_confirmation" class="peer w-full border-gray-300 rounded-lg px-4 pt-5 pb-2 focus:border-blue-600 focus:ring-0" type="password" name="password_confirmation" required placeholder=" " />
                        <label for="password_confirmation" class="absolute left-4 top-2 text-sm text-gray-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm peer-focus:text-blue-600">Confirm Password</label>
                        <button type="button" onclick="toggleVisibility('password_confirmation', 'password-confirm-icon')" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 transition">
                            <span id="password-confirm-icon">👁️</span>
                        </button>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition transform hover:-translate-y-0.5 shadow-md disabled:opacity-75 disabled:cursor-not-allowed">
                        <span id="btnText">Create Account</span>
                    </button>

                    <p class="text-center text-sm text-gray-500 mt-4">
                        Already have an account? <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Loading State Logic
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            
            btn.disabled = true;
            text.innerText = 'Creating Account...';
        });

        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerText = '🔒';
            } else {
                input.type = 'password';
                icon.innerText = '👁️';
            }
        }

        function validatePassword(password) {
            const passwordInput = document.getElementById('password');
            const checks = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password) && /[a-z]/.test(password),
                number: /[0-9]/.test(password) && /[\W_]/.test(password)
            };

            let allPassed = true;

            Object.keys(checks).forEach(id => {
                const el = document.getElementById('check-' + id);
                const dot = el.querySelector('.dot');
                if (checks[id]) {
                    el.classList.remove('text-gray-500');
                    el.classList.add('text-green-600', 'font-medium');
                    dot.classList.replace('bg-gray-300', 'bg-green-500');
                } else {
                    el.classList.add('text-gray-500');
                    el.classList.remove('text-green-600', 'font-medium');
                    dot.classList.replace('bg-green-500', 'bg-gray-300');
                    allPassed = false;
                }
            });

            if (allPassed && password.length > 0) {
                passwordInput.classList.add('border-green-500', 'bg-green-50/30');
                passwordInput.classList.remove('border-gray-300');
            } else {
                passwordInput.classList.remove('border-green-500', 'bg-green-50/30');
                passwordInput.classList.add('border-gray-300');
            }
        }
    </script>
</x-guest-layout>