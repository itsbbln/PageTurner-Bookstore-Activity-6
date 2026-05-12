@extends('layouts.app')

@section('title', 'Security Settings - PageTurner')

@section('header')
    <h1 class="text-3xl font-bold text-white">
        Security &amp; Two-Factor Authentication
    </h1>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <h2 class="text-xl font-bold mb-4">Two-Factor Authentication (2FA)</h2>

        @if (session('success'))
            <div class="mb-4 p-4 rounded bg-green-50 text-green-800 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if (session('recovery_codes'))
            <div class="mb-6 p-4 rounded bg-amber-50 border border-amber-200">
                <p class="font-semibold text-amber-900 mb-2">Save these backup recovery codes. Each can be used once if you lose access to your email.</p>
                <div class="font-mono text-sm grid grid-cols-2 gap-2 mb-2">
                    @foreach (session('recovery_codes') as $code)
                        <span class="bg-white px-2 py-1 rounded">{{ $code }}</span>
                    @endforeach
                </div>
                <p class="text-xs text-amber-700">Store them securely. They will not be shown again.</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 text-sm text-red-700 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <p class="mb-4 text-gray-700">
            Add an extra layer of security to your account. When 2FA is enabled, you will be asked to enter a code sent to your email each time you log in.
        </p>

        <p class="mb-6">
            <span class="font-semibold">Current status:</span>
            @if ($user->two_factor_enabled)
                <span class="text-green-700">Enabled (Email)</span>
            @else
                <span class="text-gray-700">Disabled</span>
            @endif
        </p>

        <div class="flex gap-4">
            @if (! $user->two_factor_enabled)
                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded bg-matcha-800 text-white hover:bg-matcha-900 transition">
                        Enable 2FA
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition">
                        Disable 2FA
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection

