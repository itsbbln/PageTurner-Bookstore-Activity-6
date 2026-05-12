@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold leading-tight text-white">
                @yield('admin_title', 'Admin')
            </h1>
            <p class="text-sm text-matcha-100">
                PageTurner Management
            </p>
        </div>
        <div class="text-sm text-matcha-100">
            {{ auth()->user()->name }}
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-3 hidden lg:block">
            <div class="lg:sticky lg:top-6">
                @include('admin.partials.sidebar')
            </div>
        </aside>

        <section class="lg:col-span-9">
            <div class="space-y-6">
                {{-- Mobile admin menu dropdown --}}
                <div class="lg:hidden">
                    <details class="rounded-lg bg-white shadow">
                        <summary class="cursor-pointer list-none px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Admin Menu</div>
                                    <div class="text-xs text-gray-500">Tap to open</div>
                                </div>
                                <span class="text-xs font-semibold text-gray-600">Menu</span>
                            </div>
                        </summary>
                        <div class="border-t p-2">
                            @include('admin.partials.menu-links')
                        </div>
                    </details>
                </div>

                @yield('admin_content')
            </div>
        </section>
    </div>
@endsection

