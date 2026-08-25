{{-- resources/views/client/sources/create.blade.php --}}
@extends('layouts.client')

@section('title', 'Connect Data Source')

@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Connect Your Database</h2>
    
    <form action="{{ route('client.sources.store') }}" method="POST" class="bg-white shadow rounded-lg p-6 space-y-6">
        @csrf
        
        <div>
            <label for="connection_name" class="block text-sm font-medium text-gray-700">Connection Name</label>
            <input type="text" name="connection_name" id="connection_name" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Production DB">
            @error('connection_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="db_type" class="block text-sm font-medium text-gray-700">Database Type</label>
            <select name="db_type" id="db_type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="mysql">MySQL</option>
                <option value="postgresql">PostgreSQL</option>
                <option value="sqlserver">SQL Server</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="host" class="block text-sm font-medium text-gray-700">Host</label>
                <input type="text" name="host" id="host" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="db.example.com">
            </div>
            <div>
                <label for="port" class="block text-sm font-medium text-gray-700">Port</label>
                <input type="number" name="port" id="port" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="3306">
            </div>
        </div>

        <div>
            <label for="database_name" class="block text-sm font-medium text-gray-700">Database Name</label>
            <input type="text" name="database_name" id="database_name" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" id="username" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('client.sources.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Test & Connect
            </button>
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('client.dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">
            Skip for Now
        </a>
    </div>
</div>
@endsection