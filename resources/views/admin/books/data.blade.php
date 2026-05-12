@extends('layouts.app')

@section('title', 'Book Data Management - PageTurner')

@section('header')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">Book Data Management</h1>
            <p class="text-sm text-matcha-100">Bulk import/export with validation, chunking, and logs</p>
        </div>
        <a href="{{ route('admin.data-management.index') }}" class="text-sm text-matcha-200 hover:text-white">← Back to Data Management</a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Import Section --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Import Books</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        XLSX/CSV with required headers:
                        <span class="font-medium text-gray-700">ISBN, Title, Author, Price, Stock, Category, Description</span>
                    </p>
                </div>
                <a href="{{ route('admin.books.data.template') }}" class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">
                    Download Template
                </a>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('admin.books.data.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Upload File</label>
                    <p class="text-xs text-gray-500 mb-3 italic">Accepted formats: .xlsx, .csv</p>
                    <input type="file" name="file" required class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-matcha-500 focus:ring-matcha-500" />
                    @error('file')
                        <div class="text-sm text-red-600 mt-2 font-medium">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-3">
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                        <label class="flex items-start gap-3 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="update_existing" value="1" class="mt-1 rounded border-gray-300 text-matcha-800 focus:ring-matcha-500" @checked(old('update_existing')) />
                            <span>
                                <span class="font-bold text-gray-900">Update Existing Records</span>
                                <span class="block text-xs text-gray-500 mt-0.5">If an ISBN already exists, update the book's details instead of skipping.</span>
                            </span>
                        </label>
                    </div>

                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                        <label class="flex items-start gap-3 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="allow_duplicate_file" value="1" class="mt-1 rounded border-gray-300 text-matcha-800 focus:ring-matcha-500" @checked(old('allow_duplicate_file')) />
                            <span>
                                <span class="font-bold text-gray-900">Allow Duplicate Uploads</span>
                                <span class="block text-xs text-gray-500 mt-0.5">Allow re-importing a file that has been uploaded previously.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2">
                    <button class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-matcha-800 text-white font-bold text-sm uppercase tracking-wider hover:bg-matcha-900 transition shadow-sm">
                        Queue Import Task
                    </button>
                    <p class="text-xs text-gray-500 italic">Imports run in the background. Check logs below for progress.</p>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Recent Import Logs</h3>
                </div>
                <div class="space-y-3">
                    @forelse ($importLogs as $log)
                        <a href="{{ route('admin.books.data.import-logs.show', $log) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-matcha-50 hover:border-matcha-200 transition group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate group-hover:text-matcha-900">{{ $log->original_filename }}</div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-bold uppercase tracking-wide
                                            @if($log->status === 'completed') bg-green-100 text-green-800
                                            @elseif($log->status === 'completed_with_errors') bg-amber-100 text-amber-800
                                            @elseif($log->status === 'failed') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif
                                        ">
                                            {{ str_replace('_',' ', $log->status) }}
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="font-medium">{{ $log->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $log->processed_rows }}/{{ $log->total_rows ?? '—' }}
                                    </div>
                                    <div class="text-[11px] font-bold @if($log->failed_rows > 0) text-red-600 @else text-gray-400 @endif uppercase">
                                        {{ $log->failed_rows }} Failed
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-sm text-gray-500 italic">No import logs available yet.</p>
                        </div>
                    @endforelse
                </div>
                @if($importLogs->hasPages())
                    <div class="mt-4">
                        {{ $importLogs->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Export Section --}}
        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900">Export Books</h2>
                <p class="text-sm text-gray-500 mt-1">Generate filtered datasets in XLSX, CSV, or PDF format.</p>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('admin.books.data.export') }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 bg-gray-50 rounded-xl border border-gray-200 p-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">File Format</label>
                        <select name="format" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                            <option value="pdf">PDF Document (.pdf)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Book Category</label>
                        <select name="category_id" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Minimum Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-sm">$</span>
                            <input type="number" step="0.01" name="min_price" class="block w-full pl-8 rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm py-2" placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Maximum Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-sm">$</span>
                            <input type="number" step="0.01" name="max_price" class="block w-full pl-8 rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm py-2" placeholder="9999.99" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Stock Status</label>
                        <select name="stock_status" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm">
                            <option value="">Any Status</option>
                            <option value="in_stock">In Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="low_stock">Low Stock (1-5 units)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">From Date</label>
                            <input type="date" name="date_from" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">To Date</label>
                            <input type="date" name="date_to" class="block w-full rounded-lg border-gray-300 text-sm focus:border-matcha-500 focus:ring-matcha-500 shadow-sm" />
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-bold text-gray-900 mb-3">Export Columns</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-white rounded-lg border border-gray-200 p-5 shadow-inner">
                            @foreach ($defaultColumns as $col => $checked)
                                <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer hover:text-matcha-800 transition">
                                    <input type="checkbox" name="columns[]" value="{{ $col }}" class="rounded border-gray-300 text-matcha-800 focus:ring-matcha-500" @checked($checked) />
                                    <span class="font-medium leading-none">{{ ucfirst(str_replace('_',' ', $col)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-2">
                    <button class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-indigo-600 text-white font-bold text-sm uppercase tracking-wider hover:bg-indigo-700 transition shadow-sm">
                        Generate & Download
                    </button>
                    <p class="text-xs text-gray-500 italic">Large datasets (>10k) will be processed in the background.</p>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Recent Export Logs</h3>
                </div>
                <div class="space-y-3">
                    @forelse ($exportLogs as $log)
                        <a href="{{ route('admin.books.data.export-logs.show', $log) }}" class="block rounded-xl border border-gray-200 p-4 hover:bg-matcha-50 hover:border-matcha-200 transition group">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate group-hover:text-matcha-900">{{ strtoupper($log->format) }} Export Dataset</div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-bold uppercase tracking-wide
                                            @if($log->status === 'completed') bg-green-100 text-green-800
                                            @elseif($log->status === 'failed') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif
                                        ">
                                            {{ $log->status }}
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="font-medium">{{ $log->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $log->record_count ?? '—' }} Records
                                    </div>
                                    <div class="text-[11px] font-bold text-indigo-600 uppercase">
                                        {{ strtoupper($log->format) }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-sm text-gray-500 italic">No export logs available yet.</p>
                        </div>
                    @endforelse
                </div>
                @if($exportLogs->hasPages())
                    <div class="mt-4">
                        {{ $exportLogs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

