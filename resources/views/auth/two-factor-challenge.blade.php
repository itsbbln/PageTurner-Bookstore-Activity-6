<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Enter the 6-digit code we emailed to you, or an 8-character recovery code.
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" value="Verification Code" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" maxlength="8" inputmode="numeric" autocomplete="one-time-code" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center gap-4">
            <x-primary-button>
                Verify
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-4">
        @csrf
        <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none">
            Resend Code
        </button>
    </form>
</x-guest-layout>

