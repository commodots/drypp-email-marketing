<x-guest-layout>
    <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900">

        <!-- LEFT SIDE (Brand / Illustration) -->
        <div
            class="hidden lg:flex w-1/2 relative overflow-hidden 
                    bg-gradient-to-br from-blue-600 to-indigo-700 
                    items-center justify-center text-white p-12">

            <!-- Subtle animated background -->
            <div
                class="absolute inset-0 opacity-20 animate-pulse 
                        bg-[radial-gradient(circle_at_30%_30%,white,transparent_40%)]">
            </div>

            <div class="relative z-10 max-w-md text-center">

                <img src="{{ asset('images/drypp_logo.png') }}" class="h-20 mx-auto mb-8">

                <h1 class="text-3xl font-bold mb-4">
                    Intelligent Email Marketing
                </h1>

                <p class="text-blue-100 leading-relaxed">
                    Send smarter campaigns, automate follow-ups,
                    and scale your outreach with Drypp.
                </p>

            </div>
        </div>

        <!-- RIGHT SIDE (Login Card) -->
        <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12">

            <div class="w-full max-w-md">

                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                    Welcome Back
                </h2>
                <p class="text-gray-500 dark:text-gray-400 mb-8">
                    Sign in to your dashboard
                </p>

                <x-auth-session-status class="mb-4 text-green-600" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Floating Email -->
                    <div class="relative">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder=" "
                            class="peer w-full border border-gray-300 
                                      dark:border-gray-600 
                                      bg-transparent rounded-lg px-4 pt-5 pb-2
                                      text-gray-900 dark:text-white
                                      focus:border-blue-600 focus:ring-0" />

                        <label for="email"
                            class="absolute left-4 top-2 text-sm 
                                      text-gray-500 dark:text-gray-400
                                      transition-all
                                      peer-placeholder-shown:top-4
                                      peer-placeholder-shown:text-base
                                      peer-placeholder-shown:text-gray-400
                                      peer-focus:top-2
                                      peer-focus:text-sm
                                      peer-focus:text-blue-600">
                            Email Address
                        </label>
                    </div>

                    <!-- Floating Password with Toggle -->
                    <div class="relative">
                        <input type="password" name="password" id="password" required placeholder=" "
                            class="peer w-full border border-gray-300 
                                      dark:border-gray-600
                                      bg-transparent rounded-lg px-4 pt-5 pb-2
                                      text-gray-900 dark:text-white
                                      focus:border-blue-600 focus:ring-0" />

                        <label for="password"
                            class="absolute left-4 top-2 text-sm 
                                      text-gray-500 dark:text-gray-400
                                      transition-all
                                      peer-placeholder-shown:top-4
                                      peer-placeholder-shown:text-base
                                      peer-placeholder-shown:text-gray-400
                                      peer-focus:top-2
                                      peer-focus:text-sm
                                      peer-focus:text-blue-600">
                            Password
                        </label>

                        <!-- Toggle -->
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-4 text-gray-400 
                                       hover:text-gray-600 dark:hover:text-gray-300">
                            👁
                        </button>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                            <span class="text-gray-600 dark:text-gray-400">
                                Remember me
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-blue-600 hover:text-blue-800 font-medium">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700
                                   text-white font-semibold py-3 rounded-lg
                                   transition transform hover:-translate-y-0.5">
                        Sign In
                    </button>

                    <p class="text-center text-sm text-gray-500 mt-6 dark:text-gray-400">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline transition">
                            Create one for free
                        </a>
                    </p>
                </form>

                <!-- Divider -->
                <div class="my-6 flex items-center">
                    <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
                    <span class="mx-4 text-sm text-gray-400">OR</span>
                    <div class="flex-grow border-t border-gray-300 dark:border-gray-600"></div>
                </div>

                <!-- Social Buttons -->
                <div class="space-y-3">

                    <button
                        class="w-full border border-gray-300 dark:border-gray-600
                                   py-2 rounded-lg flex items-center justify-center
                                   hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Continue with Google
                    </button>

                    <button
                        class="w-full border border-gray-300 dark:border-gray-600
                                   py-2 rounded-lg flex items-center justify-center
                                   hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Continue with Microsoft
                    </button>

                </div>

            </div>
        </div>
    </div>

    <!-- Password Toggle Script -->
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const btn = event.currentTarget;
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerText = '🙈';
            } else {
                input.type = 'password';
                btn.innerText = '👁';
            }
        }
    </script>
</x-guest-layout>
