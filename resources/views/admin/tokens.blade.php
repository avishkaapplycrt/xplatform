@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Analytics API Tokens</h1>
    
    <form action="{{ route('admin.tokens.generate') }}" method="POST" class="mb-6">
        @csrf
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Generate New Token</button>
    </form>

    <table class="w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Token (Hashed)</th>
                <th class="px-4 py-2 text-left">Last Used</th>
                <th class="px-4 py-2 text-left">Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tokens as $token)
            <tr class="border-t">
                <td class="px-4 py-2">{{ $token->name }}</td>
                <td class="px-4 py-2 font-mono text-xs">{{ substr($token->token, 0, 20) }}...</td>
                <td class="px-4 py-2">{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}</td>
                <td class="px-4 py-2">{{ $token->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection