<x-guest-layout title="Reset Password" backUrl="{{ route('client.login') }}" previousUrl="{{ route('client.login') }}" :currentStep="1">

    <div class="px-10 py-10">

        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor"
                     stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Set new password</h2>
            <p class="text-sm text-gray-400 mt-2">Must be at least 8 characters with letters and numbers.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-5">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-4">
                <x-input-label for="email" :value="__('Email address')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email"
                    name="email" :value="old('email', $email)"
                    placeholder="you@company.com"
                    required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="password" :value="__('New password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password"
                    name="password"
                    placeholder="Min 8 characters, letters &amp; numbers"
                    required autofocus autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                {{-- Password strength bar --}}
                <div class="mt-2">
                    <div class="flex gap-1" id="strength-bars">
                        <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar1"></div>
                        <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar2"></div>
                        <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar3"></div>
                        <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar4"></div>
                    </div>
                    <p id="strength-label" class="text-xs text-gray-400 mt-1"></p>
                </div>
            </div>

            <div class="mb-6">
                <x-input-label for="password_confirmation" :value="__('Confirm new password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation"
                    placeholder="Repeat your new password"
                    required autocomplete="new-password" />
                <p id="match-error" class="hidden text-xs text-red-500 mt-1">Passwords do not match.</p>
            </div>

            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 rounded-lg transition">
                Reset password
            </button>

        </form>

    </div>

</x-guest-layout>

<script>
(function () {
    const pwInput    = document.getElementById('password');
    const confInput  = document.getElementById('password_confirmation');
    const bars       = [document.getElementById('bar1'), document.getElementById('bar2'),
                        document.getElementById('bar3'), document.getElementById('bar4')];
    const label      = document.getElementById('strength-label');
    const matchError = document.getElementById('match-error');

    function score(pw) {
        let s = 0;
        if (pw.length >= 8)                     s++;
        if (pw.length >= 12)                    s++;
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
        if (/[0-9]/.test(pw))                   s++;
        if (/[^A-Za-z0-9]/.test(pw))            s = Math.min(s + 1, 4);
        return Math.min(s, 4);
    }

    const colors = ['', 'bg-red-400', 'bg-amber-400', 'bg-yellow-400', 'bg-green-500'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

    pwInput.addEventListener('input', function () {
        const s = score(this.value);
        bars.forEach((b, i) => {
            b.className = 'h-1 flex-1 rounded-full ' + (i < s ? colors[s] : 'bg-gray-200');
        });
        label.textContent = this.value.length ? labels[s] : '';
        label.className   = 'text-xs mt-1 ' + (s <= 1 ? 'text-red-400' : s <= 2 ? 'text-amber-500' : s === 3 ? 'text-yellow-600' : 'text-green-600');
    });

    confInput.addEventListener('input', function () {
        if (this.value && this.value !== pwInput.value) {
            matchError.classList.remove('hidden');
        } else {
            matchError.classList.add('hidden');
        }
    });
})();
</script>
