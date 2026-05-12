@extends('layouts.app')

@section('title', 'Profile')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">
            Profile
        </h1>
        <a href="{{ route('dashboard') }}"
           class="hidden sm:inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
            ← Back to dashboard
        </a>
    </div>
@endsection

@section('content')
    <div class="mb-4 sm:hidden">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
            ← Back to dashboard
        </a>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded shadow">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <div class="max-w-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Two-Factor Authentication (2FA)</h3>
                <p class="text-sm text-gray-600 mb-4">Add an extra layer of security. Enable or disable 2FA from your profile.</p>
                <a href="{{ route('two-factor.settings') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-matcha-800 text-white text-sm font-medium hover:bg-matcha-900 transition">
                    Manage 2FA Settings
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
